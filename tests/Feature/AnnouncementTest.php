<?php

use App\Enums\Role;
use App\Models\Announcement;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');
});

it('lets an admin publish an announcement with a poster', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->post(route('announcements.store'), [
            'title' => 'Blood donation drive',
            'body' => 'Level G, 9am to 12.30pm.',
            'image' => UploadedFile::fake()->image('poster.jpg', 800, 600),
            'sort_order' => 1,
            'is_active' => 1,
        ])
        ->assertRedirect(route('announcements.index'))
        ->assertSessionHas('status');

    $announcement = Announcement::query()->sole();
    expect($announcement->title)->toBe('Blood donation drive')
        ->and($announcement->created_by)->toBe($admin->id);
    Storage::disk('public')->assertExists($announcement->image_path);
});

it('requires a poster when creating', function () {
    $this->actingAs(User::factory()->admin()->create())
        ->post(route('announcements.store'), ['title' => 'No poster', 'sort_order' => 0, 'is_active' => 1])
        ->assertSessionHasErrors('image');

    expect(Announcement::query()->count())->toBe(0);
});

it('updates, hides and deletes an announcement and its poster', function () {
    $admin = User::factory()->admin()->create();
    $path = UploadedFile::fake()->image('old.jpg')->store('announcements', 'public');
    $announcement = Announcement::factory()->create(['image_path' => $path]);

    $this->actingAs($admin)
        ->put(route('announcements.update', $announcement), [
            'title' => 'Renamed',
            'image' => UploadedFile::fake()->image('new.png'),
            'sort_order' => 2,
            'is_active' => 1,
        ])
        ->assertRedirect();

    $announcement->refresh();
    expect($announcement->title)->toBe('Renamed')->and($announcement->image_path)->not->toBe($path);
    Storage::disk('public')->assertMissing($path);

    $this->actingAs($admin)->patch(route('announcements.toggle', $announcement))->assertRedirect();
    expect($announcement->refresh()->is_active)->toBeFalse();

    $newPath = $announcement->image_path;
    $this->actingAs($admin)->delete(route('announcements.destroy', $announcement))->assertRedirect();
    expect(Announcement::query()->count())->toBe(0);
    Storage::disk('public')->assertMissing($newPath);
});

it('keeps doctors and schedulers out of announcement admin', function (Role $role) {
    [, $doctors] = rosterDepartment(1);
    $user = $doctors->first();
    $user->update(['role' => $role]);

    $this->actingAs($user)->get(route('announcements.index'))->assertRedirect(route('unauthorized'));
})->with([Role::Scheduler, Role::Regular]);

it('only shows active announcements inside their window', function () {
    $live = Announcement::factory()->create(['title' => 'Live one']);
    Announcement::factory()->create(['title' => 'Hidden one', 'is_active' => false]);
    Announcement::factory()->create(['title' => 'Future one', 'published_at' => now()->addDay()]);
    Announcement::factory()->create(['title' => 'Old one', 'expires_at' => now()->subHour()]);

    expect(Announcement::query()->visible()->pluck('id')->all())->toBe([$live->id]);
});

it('switches off expired announcements on schedule', function () {
    $expired = Announcement::factory()->create(['expires_at' => now()->subMinute()]);
    $current = Announcement::factory()->create(['expires_at' => now()->addDay()]);

    $this->artisan('hss:expire-announcements')->expectsOutputToContain('Expired 1')->assertSuccessful();

    expect($expired->refresh()->is_active)->toBeFalse()
        ->and($current->refresh()->is_active)->toBeTrue();
});

it('shows the carousel on the landing page and the dashboard', function () {
    Announcement::factory()->create(['title' => 'Farmasi MyUBAT', 'image_path' => 'announcements/x.jpg']);
    Announcement::factory()->create(['title' => 'Not yet', 'published_at' => now()->addWeek()]);

    $this->get('/')->assertOk()->assertSee('data-carousel', false)->assertSee('Farmasi MyUBAT')->assertDontSee('Not yet');

    [$department, $doctors] = rosterDepartment(1);
    $this->actingAs($doctors->first())->get(route('dashboard'))->assertOk()->assertSee('Farmasi MyUBAT');
});

it('filters the staff list by name or email', function () {
    $admin = User::factory()->admin()->create();
    [, $doctors] = rosterDepartment(2);
    $doctors[0]->update(['name' => 'Dr Aisyah Rahman']);
    $doctors[1]->update(['name' => 'Dr Bala Kumar']);

    $this->actingAs($admin)->get(route('manageStaff', ['q' => 'aisyah']))
        ->assertOk()->assertSee('Dr Aisyah Rahman')->assertDontSee('Dr Bala Kumar');
});
