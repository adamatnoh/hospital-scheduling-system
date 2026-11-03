<?php

use App\Models\User;

beforeEach(function () {
    config(['hss.demo_login' => true]);
});

dataset('demo accounts', [
    'admin' => ['admin', 'admin@hss.test'],
    'scheduler' => ['scheduler', 'scheduler@hss.test'],
    'doctor' => ['doctor', 'doctor@hss.test'],
]);

it('signs a visitor into each seeded demo account', function (string $role, string $email) {
    $user = User::factory()->create(['email' => $email]);

    $this->post(route('demo-login', $role))
        ->assertRedirect(route('dashboard'));

    $this->assertAuthenticatedAs($user);
})->with('demo accounts');

it('shows the demo buttons on the login page when enabled', function () {
    $this->get('/login')
        ->assertOk()
        ->assertSee('Demo accounts')
        ->assertSee(route('demo-login', 'admin'))
        ->assertSee(route('demo-login', 'scheduler'))
        ->assertSee(route('demo-login', 'doctor'));
});

it('hides the buttons and refuses demo sign-in when disabled', function () {
    config(['hss.demo_login' => false]);
    User::factory()->create(['email' => 'admin@hss.test']);

    $this->get('/login')->assertOk()->assertDontSee('Demo accounts');
    $this->post(route('demo-login', 'admin'))->assertNotFound();
    $this->assertGuest();
});

it('only reaches the three known demo roles', function () {
    User::factory()->create(['email' => 'someone@hss.test']);

    $this->post('/demo-login/someone')->assertNotFound();
    $this->assertGuest();
});

it('runs demo sign-in through the CSRF-protected web stack, POST only', function () {
    $route = app('router')->getRoutes()->getByName('demo-login');

    expect($route->methods())->toBe(['POST'])
        ->and($route->middleware())->toContain('web', 'guest', 'throttle:10,1');

    $this->get('/demo-login/admin')->assertMethodNotAllowed();
});

it('reports a missing demo account instead of failing', function () {
    $this->post(route('demo-login', 'doctor'))
        ->assertRedirect(route('login'))
        ->assertSessionHasErrors('email');
    $this->assertGuest();
});

it('throttles repeated demo sign-in attempts', function () {
    foreach (range(1, 10) as $_) {
        $this->post(route('demo-login', 'doctor'));
    }

    $this->post(route('demo-login', 'doctor'))->assertStatus(429);
});

it('seeds the demo accounts with their display names', function (string $role, string $name) {
    $this->seed();

    $this->post(route('demo-login', $role))->assertRedirect(route('dashboard'));

    $this->get(route('dashboard'))
        ->assertOk()
        ->assertSee($name)
        ->assertDontSee('Dr Dr');
})->with([
    'admin' => ['admin', 'Dr Adam'],
    'scheduler' => ['scheduler', 'Dr Farah'],
    'doctor' => ['doctor', 'Dr Firdaus'],
]);
