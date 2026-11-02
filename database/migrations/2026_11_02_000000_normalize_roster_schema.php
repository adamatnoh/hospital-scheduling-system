<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->unsignedTinyInteger('doctors_per_on_call_day')->default(2);
            $table->unsignedTinyInteger('monthly_on_call_cap')->default(6);
            $table->boolean('rest_after_night')->default(false);
            $table->boolean('include_weekends')->default(false);
            $table->unsignedTinyInteger('ward_block_days')->default(14);
            $table->string('holiday_state')->default('Johor');
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $table->string('calendar_token', 64)->nullable()->unique();
        });

        if (Schema::hasColumn('users', 'department')) {
            $names = DB::table('users')->whereNotNull('department')->distinct()->pluck('department');
            foreach ($names as $name) {
                $id = DB::table('departments')->where('name', $name)->value('id');
                if ($id === null) {
                    $id = DB::table('departments')->insertGetId([
                        'name' => $name,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
                DB::table('users')->where('department', $name)->update(['department_id' => $id]);
            }
        }

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'department')) {
                $table->dropColumn('department');
            }
            if (Schema::hasColumn('users', 'current_team_id')) {
                $table->dropColumn('current_team_id');
            }
            if (Schema::hasColumn('users', 'profile_photo_path')) {
                $table->dropColumn('profile_photo_path');
            }
        });

        $legacyWards = Schema::hasTable('wards') ? DB::table('wards')->get() : collect();
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('wards');
        Schema::enableForeignKeyConstraints();

        Schema::create('wards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('colour', 7)->nullable();
            $table->timestamps();
            $table->unique(['department_id', 'name']);
        });

        foreach ($legacyWards as $ward) {
            $departmentId = DB::table('departments')->where('name', $ward->department)->value('id');
            if ($departmentId === null) {
                continue;
            }
            DB::table('wards')->insert([
                'department_id' => $departmentId,
                'name' => $ward->name,
                'created_at' => $ward->created_at ?? now(),
                'updated_at' => $ward->updated_at ?? now(),
            ]);
        }

        $legacyHolidays = Schema::hasTable('public_holidays') ? DB::table('public_holidays')->get() : collect();
        Schema::dropIfExists('public_holidays');
        Schema::create('public_holidays', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->string('name');
            $table->string('state')->default('National');
            $table->timestamps();
            $table->unique(['date', 'state']);
        });

        foreach ($legacyHolidays as $holiday) {
            $date = substr((string) $holiday->start_date, 0, 10);
            DB::table('public_holidays')->insertOrIgnore([
                'date' => $date,
                'name' => $holiday->title,
                'state' => $holiday->states ?: 'National',
                'created_at' => $holiday->created_at ?? now(),
                'updated_at' => $holiday->updated_at ?? now(),
            ]);
        }

        Schema::create('schedule_runs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')->constrained()->cascadeOnDelete();
            $table->string('type', 20);
            $table->unsignedSmallInteger('year');
            $table->unsignedTinyInteger('month');
            $table->string('status', 20)->default('preview');
            $table->json('fairness')->nullable();
            $table->foreignId('generated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->index(['department_id', 'type', 'year', 'month', 'status'], 'runs_dept_period_idx');
        });

        Schema::create('schedule_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('schedule_run_id')->constrained()->cascadeOnDelete();
            $table->string('type', 20);
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('department_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ward_id')->nullable()->constrained()->nullOnDelete();
            $table->string('shift', 20)->nullable();
            $table->date('date');
            $table->boolean('is_published')->default(false);
            $table->timestamps();
            $table->unique(['schedule_run_id', 'user_id', 'date', 'type'], 'assignments_run_user_date_type');
            $table->index(['department_id', 'type', 'date', 'is_published'], 'assignments_lookup_idx');
        });

        Schema::create('schedule_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('department_id')->constrained()->cascadeOnDelete();
            $table->string('type', 20);
            $table->date('start_date');
            $table->date('end_date');
            $table->text('reason');
            $table->string('status', 20)->default('pending');
            $table->text('rejection_reason')->nullable();
            $table->foreignId('reviewer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->foreignId('assignment_id')->nullable()->constrained('schedule_assignments')->nullOnDelete();
            $table->foreignId('counterpart_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('counterpart_assignment_id')->nullable()->constrained('schedule_assignments')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action');
            $table->string('subject_type')->nullable();
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->json('properties')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['subject_type', 'subject_id']);
        });

        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->morphs('notifiable');
            $table->text('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });

        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('assigns');
        Schema::dropIfExists('assign_wards');
        Schema::dropIfExists('assign_shifts');
        Schema::dropIfExists('leave_applications');
        Schema::dropIfExists('on_call_applications');
        Schema::dropIfExists('department_schedules');
        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('schedule_requests');
        Schema::dropIfExists('schedule_assignments');
        Schema::dropIfExists('schedule_runs');
        Schema::dropIfExists('public_holidays');
        Schema::dropIfExists('wards');

        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('department_id');
            $table->dropColumn('calendar_token');
            $table->string('department')->nullable();
        });

        Schema::dropIfExists('departments');
    }
};
