<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('hss:remind-on-call')->dailyAt('08:00');
