<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Schedule::command('backup:run')->dailyAt('02:00');
Schedule::command('inbound-documents:send-deadline-alerts')->dailyAt('08:00');