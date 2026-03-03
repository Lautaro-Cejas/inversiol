<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('broker:sync')->everyFifteenMinutes()->weekdays()->between('11:00', '17:00');
Schedule::command('broker:cazar')->everyFiveMinutes()->weekdays()->between('11:00', '17:00');
Schedule::command('broker:trade --execute')->everyFiveMinutes()->weekdays()->between('11:00', '17:00');