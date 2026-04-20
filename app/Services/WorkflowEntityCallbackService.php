<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\WorkflowInstance;

class WorkflowEntityCallbackService
{
    public function onFinalApproval(WorkflowInstance $instance): void
    {
        if ($instance->source_type === Booking::class) {
            $booking = Booking::with('unit')->find($instance->source_id);

            if ($booking) {
                $booking->update(['status' => 'cancelled']);

                if ($booking->unit) {
                    $booking->unit->update(['status' => 'available']);
                }
            }
        }
    }

    public function onRejected(WorkflowInstance $instance): void
    {
        // Dispatch notification/event to requester in a real implementation.
    }
}
