<?php

namespace Database\Seeders;

use App\Enums\AssignmentType;
use App\Enums\RequestStatus;
use App\Enums\RequestType;
use App\Enums\Role;
use App\Models\Department;
use App\Models\PublicHoliday;
use App\Models\ScheduleRequest;
use App\Models\User;
use App\Models\Ward;
use App\Services\Scheduling\ScheduleGenerator;
use App\Services\Scheduling\SchedulePublisher;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $orthopedics = Department::query()->create([
            'name' => 'Orthopedics',
            'doctors_per_on_call_day' => 2,
            'monthly_on_call_cap' => 8,
            'rest_after_night' => false,
            'include_weekends' => false,
            'ward_block_days' => 14,
            'holiday_state' => 'Johor',
        ]);

        $emergency = Department::query()->create([
            'name' => 'Emergency',
            'doctors_per_on_call_day' => 2,
            'monthly_on_call_cap' => 8,
            'rest_after_night' => true,
            'include_weekends' => true,
            'ward_block_days' => 14,
            'holiday_state' => 'Johor',
        ]);

        foreach (['Male Ortho' => '#8da399', 'Female Ortho' => '#d65302', 'Trauma' => '#311F62'] as $name => $colour) {
            Ward::query()->create(['department_id' => $orthopedics->id, 'name' => $name, 'colour' => $colour]);
        }
        foreach (['Red Zone' => '#d65302', 'Green Zone' => '#8da399'] as $name => $colour) {
            Ward::query()->create(['department_id' => $emergency->id, 'name' => $name, 'colour' => $colour]);
        }

        $this->holidays();

        // Doctor names are stored without the title; User::displayName() adds "Dr"
        // for schedulers and doctors. Admins are shown as stored.
        User::query()->create([
            'name' => 'Dr Adam',
            'email' => 'admin@hss.test',
            'password' => Hash::make('password'),
            'role' => Role::Admin,
            'department_id' => null,
        ]);

        $scheduler = User::query()->create([
            'name' => 'Farah',
            'email' => 'scheduler@hss.test',
            'password' => Hash::make('password'),
            'role' => Role::Scheduler,
            'department_id' => $orthopedics->id,
        ]);

        $doctor = User::query()->create([
            'name' => 'Firdaus',
            'email' => 'doctor@hss.test',
            'password' => Hash::make('password'),
            'role' => Role::Regular,
            'department_id' => $orthopedics->id,
        ]);

        foreach (['Farid Osman', 'Mei Ling Tan', 'Hafiz Abdullah', 'Siti Aminah'] as $name) {
            User::query()->create([
                'name' => $name,
                'email' => strtolower(str_replace(' ', '.', $name)).'@hss.test',
                'password' => Hash::make('password'),
                'role' => Role::Regular,
                'department_id' => $orthopedics->id,
            ]);
        }

        $emergencyScheduler = User::query()->create([
            'name' => 'Nadia Yusuf',
            'email' => 'emergency.scheduler@hss.test',
            'password' => Hash::make('password'),
            'role' => Role::Scheduler,
            'department_id' => $emergency->id,
        ]);

        foreach (['Johan Lee', 'Priya Nair', 'Kamal Idris'] as $name) {
            User::query()->create([
                'name' => $name,
                'email' => strtolower(str_replace(' ', '.', $name)).'@hss.test',
                'password' => Hash::make('password'),
                'role' => Role::Regular,
                'department_id' => $emergency->id,
            ]);
        }

        ScheduleRequest::query()->create([
            'user_id' => $doctor->id,
            'department_id' => $orthopedics->id,
            'type' => RequestType::Leave,
            'start_date' => now()->addDays(10)->toDateString(),
            'end_date' => now()->addDays(12)->toDateString(),
            'reason' => 'Family wedding in Kluang',
            'status' => RequestStatus::Pending,
        ]);

        ScheduleRequest::query()->create([
            'user_id' => $doctor->id,
            'department_id' => $orthopedics->id,
            'type' => RequestType::OnCall,
            'start_date' => now()->startOfMonth()->addDays(4)->toDateString(),
            'end_date' => now()->startOfMonth()->addDays(4)->toDateString(),
            'reason' => 'Prefer this date before the wedding',
            'status' => RequestStatus::Approved,
        ]);

        $generator = app(ScheduleGenerator::class);
        $publisher = app(SchedulePublisher::class);
        $period = now();

        foreach ([$orthopedics, $emergency] as $department) {
            foreach (AssignmentType::cases() as $type) {
                $run = $generator->preview($department, $type, (int) $period->year, (int) $period->month, $scheduler->id);
                $publisher->publish($run);
            }
        }

        $this->call(AnnouncementSeeder::class);

        unset($emergencyScheduler);
    }

    private function holidays(): void
    {
        $year = (int) now()->year;
        $fixed = [
            [$year.'-01-01', 'New Year', 'National'],
            [$year.'-05-01', 'Labour Day', 'National'],
            [$year.'-08-31', 'National Day', 'National'],
            [$year.'-09-16', 'Malaysia Day', 'National'],
            [$year.'-12-25', 'Christmas', 'National'],
            [$year.'-03-23', 'Sultan of Johor Birthday', 'Johor'],
        ];

        foreach ($fixed as [$date, $name, $state]) {
            PublicHoliday::query()->create(compact('date', 'name', 'state'));
        }
    }
}
