<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

// BREAKFAST
Schedule::command('update-diet-oral BREAKFAST AM 4:30')
    ->dailyAt('4:30');  // 5:00

// LUNCH
Schedule::command('update-diet-oral LUNCH PM 10:00')
    ->dailyAt('10:00'); // 10:30

// DINNER
Schedule::command('update-diet-oral DINNER MN 15:00')
    ->dailyAt('15:00'); // 16:00

// TODAY - for enteral
Schedule::command('update-diet-enteral TODAY 00:00')
    ->dailyAt('0:00');
