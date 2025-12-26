<?php

namespace App\Jobs;

use App\Models\Ride;
use App\Models\RideParticipant;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class MarkNoShowParticipants implements ShouldQueue
{
    use Queueable, SerializesModels;

    protected $rideId;

    /**
     * Create a new job instance.
     */
    public function __construct(int $rideId)
    {
        $this->rideId = $rideId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $ride = Ride::find($this->rideId);

            if (!$ride || !$ride->end_time) {
                return;
            }

            // Only process if ride has ended
            if ($ride->end_time->isFuture()) {
                return;
            }

            // Get all participants who:
            // - Status is 'joined'
            // - cancelled_at is null
            // - excused_at is null
            // - checked_at is null
            $participants = RideParticipant::where('ride_id', $this->rideId)
                ->where('status', 'joined')
                ->whereNull('cancelled_at')
                ->whereNull('excused_at')
                ->whereNull('checked_at')
                ->get();

            foreach ($participants as $participant) {
                $participant->update([
                    'status' => 'no_show',
                    'checked_at' => now(),
                ]);
            }

            Log::info("Marked {$participants->count()} participants as no_show for ride {$this->rideId}");

        } catch (\Exception $e) {
            Log::error('Error in MarkNoShowParticipants job: ' . $e->getMessage());
        }
    }
}
