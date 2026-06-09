<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Sweep for dormant leads every morning so nothing quietly goes cold.
Schedule::command('clients:flag-dormant')->dailyAt('02:00');
