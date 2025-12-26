<?php

use App\Jobs\MarkNoShowParticipants;
use App\Models\Ride;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule job to mark no-show participants after ride ends
Schedule::call(function () {
    // Get all rides that have ended in the last hour
    $endedRides = Ride::where('end_time', '<=', now())
        ->where('end_time', '>=', now()->subHour())
        ->get();

    foreach ($endedRides as $ride) {
        MarkNoShowParticipants::dispatch($ride->id);
    }
})->hourly();
