<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkflowEngineTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_triggers_an_instance_and_approves_all_steps(): void
    {
        $this->seed();

        $coordinator = User::where('email', 'beyonce@example.com')->firstOrFail();
        $booking = Booking::where('booking_no', 'BK-001')->firstOrFail();

        $triggerResponse = $this->postJson('/api/workflow-instances/trigger', [
            'event_name' => 'booking.cancellation_requested',
            'source_type' => Booking::class,
            'source_id' => $booking->id,
            'requested_by_user_id' => $coordinator->id,
        ]);

        $triggerResponse->assertCreated();

        $instanceId = $triggerResponse->json('id');
        $firstStepId = collect($triggerResponse->json('steps'))->firstWhere('sequence', 1)['id'];

        $salesManager = User::where('email', 'beckham@example.com')->firstOrFail();

        $this->postJson("/api/workflow-instance-steps/{$firstStepId}/approve", [
            'user_id' => $salesManager->id,
            'comment' => 'Approved by sales manager.',
        ])->assertOk();

        $instanceResponse = $this->getJson("/api/workflow-instances/{$instanceId}");
        $secondStepId = collect($instanceResponse->json('steps'))->firstWhere('sequence', 2)['id'];

        $financeManager = User::where('email', 'farid@example.com')->firstOrFail();

        $this->postJson("/api/workflow-instance-steps/{$secondStepId}/approve", [
            'user_id' => $financeManager->id,
            'comment' => 'Finance approved.',
        ])->assertOk();

        $this->assertDatabaseHas('workflow_instances', [
            'id' => $instanceId,
            'status' => 'approved',
        ]);

        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'status' => 'cancelled',
        ]);
    }

    public function test_it_prevents_duplicate_running_instance_for_same_entity(): void
    {
        $this->seed();

        $coordinator = User::where('email', 'beyonce@example.com')->firstOrFail();
        $booking = Booking::where('booking_no', 'BK-002')->firstOrFail();

        $payload = [
            'event_name' => 'booking.cancellation_requested',
            'source_type' => Booking::class,
            'source_id' => $booking->id,
            'requested_by_user_id' => $coordinator->id,
        ];

        $this->postJson('/api/workflow-instances/trigger', $payload)->assertCreated();
        $this->postJson('/api/workflow-instances/trigger', $payload)->assertStatus(409);
    }

    public function test_it_rejects_unauthorized_approver(): void
    {
        $this->seed();

        $coordinator = User::where('email', 'beyonce@example.com')->firstOrFail();
        $booking = Booking::where('booking_no', 'BK-003')->firstOrFail();

        $triggerResponse = $this->postJson('/api/workflow-instances/trigger', [
            'event_name' => 'booking.cancellation_requested',
            'source_type' => Booking::class,
            'source_id' => $booking->id,
            'requested_by_user_id' => $coordinator->id,
        ]);

        $stepId = collect($triggerResponse->json('steps'))->firstWhere('sequence', 1)['id'];

        $this->postJson("/api/workflow-instance-steps/{$stepId}/approve", [
            'user_id' => $coordinator->id,
            'comment' => 'Trying to approve without permission.',
        ])->assertStatus(403);
    }
}
