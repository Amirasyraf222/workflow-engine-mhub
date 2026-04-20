<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\TriggerWorkflowInstanceRequest;
use App\Models\WorkflowInstance;
use App\Services\WorkflowEngineService;
use Illuminate\Http\JsonResponse;

class WorkflowInstanceController extends Controller
{
    public function __construct(private WorkflowEngineService $service)
    {
    }

    public function trigger(TriggerWorkflowInstanceRequest $request): JsonResponse
    {
        try {
            $instance = $this->service->trigger(
                $request->validated()['event_name'],
                $request->validated()['source_type'],
                (int) $request->validated()['source_id'],
                (int) $request->validated()['requested_by_user_id']
            );
        } catch (\DomainException $e) {
            return response()->json(['message' => $e->getMessage()], 409);
        }

        return response()->json($instance->load(['steps.assignedUser', 'steps.assignedRole']), 201);
    }

    public function show(WorkflowInstance $workflowInstance): JsonResponse
    {
        return response()->json(
            $workflowInstance->load([
                'template',
                'requester',
                'steps.assignedUser',
                'steps.assignedRole',
                'steps.actionedByUser',
                'steps.actions.actor',
            ])
        );
    }
}
