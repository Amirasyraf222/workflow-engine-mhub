<?php

namespace App\Services;

use App\Models\WorkflowInstance;
use App\Models\WorkflowInstanceStep;
use App\Models\WorkflowStepAction;
use App\Models\WorkflowTemplate;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\HttpException;

class WorkflowEngineService
{
    public function __construct(private WorkflowEntityCallbackService $callbackService)
    {
    }

    public function trigger(string $eventName, string $sourceType, int $sourceId, int $requestedByUserId): WorkflowInstance
    {
        $template = WorkflowTemplate::with('steps')
            ->where('trigger_event', $eventName)
            ->where('is_active', true)
            ->first();

        if (!$template) {
            throw new \DomainException("No active workflow template found for event [{$eventName}].");
        }

        $existing = WorkflowInstance::query()
            ->where('source_type', $sourceType)
            ->where('source_id', $sourceId)
            ->whereIn('status', ['pending', 'in_progress'])
            ->first();

        if ($existing) {
            throw new \DomainException("A running workflow instance already exists for this entity. Existing instance ID: {$existing->id}");
        }

        return DB::transaction(function () use ($template, $eventName, $sourceType, $sourceId, $requestedByUserId) {
            $instance = WorkflowInstance::create([
                'workflow_template_id' => $template->id,
                'trigger_event' => $eventName,
                'source_type' => $sourceType,
                'source_id' => $sourceId,
                'status' => 'pending',
                'requested_by_user_id' => $requestedByUserId,
            ]);

            $firstSequence = $template->steps->min('sequence');

            foreach ($template->steps->sortBy('sequence') as $templateStep) {
                $instance->steps()->create([
                    'sequence' => $templateStep->sequence,
                    'status' => $templateStep->sequence === $firstSequence ? 'awaiting_action' : 'pending',
                    'assigned_user_id' => $templateStep->approver_type === 'user' ? $templateStep->user_id : null,
                    'assigned_role_id' => $templateStep->approver_type === 'role' ? $templateStep->role_id : null,
                ]);
            }

            $instance->update(['status' => 'in_progress']);

            return $instance->fresh();
        });
    }

    public function approveStep(int $stepId, int $userId, ?string $comment): WorkflowInstanceStep
    {
        return DB::transaction(function () use ($stepId, $userId, $comment) {
            $step = WorkflowInstanceStep::with('instance')
                ->whereKey($stepId)
                ->lockForUpdate()
                ->first();

            if (!$step) {
                throw new HttpException(404, 'Workflow step not found.');
            }

            if ($step->status !== 'awaiting_action') {
                throw new \DomainException('Step is not awaiting action.');
            }

            $this->assertUserCanAct($step, $userId);

            $step->update([
                'status' => 'approved',
                'actioned_by' => $userId,
                'decision' => 'approved',
                'comment' => $comment,
                'actioned_at' => now(),
            ]);

            WorkflowStepAction::create([
                'workflow_instance_step_id' => $step->id,
                'workflow_instance_id' => $step->workflow_instance_id,
                'acted_by' => $userId,
                'decision' => 'approved',
                'comment' => $comment,
                'acted_at' => now(),
            ]);

            $nextStep = WorkflowInstanceStep::query()
                ->where('workflow_instance_id', $step->workflow_instance_id)
                ->where('sequence', '>', $step->sequence)
                ->orderBy('sequence')
                ->lockForUpdate()
                ->first();

            if ($nextStep) {
                $nextStep->update(['status' => 'awaiting_action']);
                $step->instance->update(['status' => 'in_progress']);
            } else {
                $step->instance->update([
                    'status' => 'approved',
                    'completed_at' => now(),
                ]);

                $this->callbackService->onFinalApproval($step->instance->fresh());
            }

            return $step->fresh();
        });
    }

    public function rejectStep(int $stepId, int $userId, string $comment): WorkflowInstanceStep
    {
        return DB::transaction(function () use ($stepId, $userId, $comment) {
            $step = WorkflowInstanceStep::with('instance')
                ->whereKey($stepId)
                ->lockForUpdate()
                ->first();

            if (!$step) {
                throw new HttpException(404, 'Workflow step not found.');
            }

            if ($step->status !== 'awaiting_action') {
                throw new \DomainException('Step is not awaiting action.');
            }

            $this->assertUserCanAct($step, $userId);

            $step->update([
                'status' => 'rejected',
                'actioned_by' => $userId,
                'decision' => 'rejected',
                'comment' => $comment,
                'actioned_at' => now(),
            ]);

            WorkflowStepAction::create([
                'workflow_instance_step_id' => $step->id,
                'workflow_instance_id' => $step->workflow_instance_id,
                'acted_by' => $userId,
                'decision' => 'rejected',
                'comment' => $comment,
                'acted_at' => now(),
            ]);

            $step->instance->update([
                'status' => 'rejected',
                'completed_at' => now(),
            ]);

            $this->callbackService->onRejected($step->instance->fresh());

            return $step->fresh();
        });
    }

    private function assertUserCanAct(WorkflowInstanceStep $step, int $userId): void
    {
        if ($step->assigned_user_id && $step->assigned_user_id === $userId) {
            return;
        }

        if ($step->assigned_role_id) {
            $hasRole = DB::table('role_user')
                ->where('role_id', $step->assigned_role_id)
                ->where('user_id', $userId)
                ->exists();

            if ($hasRole) {
                return;
            }
        }

        throw new HttpException(403, 'You are not allowed to act on this step.');
    }
}
