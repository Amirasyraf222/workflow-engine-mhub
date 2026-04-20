<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ApproveWorkflowStepRequest;
use App\Http\Requests\RejectWorkflowStepRequest;
use App\Models\WorkflowInstanceStep;
use App\Services\WorkflowEngineService;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;

class WorkflowInstanceActionController extends Controller
{
    public function __construct(private WorkflowEngineService $service)
    {
    }

    public function approve(ApproveWorkflowStepRequest $request, WorkflowInstanceStep $workflowInstanceStep): JsonResponse
    {
        try {
            $step = $this->service->approveStep(
                $workflowInstanceStep->id,
                (int) $request->validated()['user_id'],
                $request->validated()['comment'] ?? null
            );
        } catch (\DomainException $e) {
            return response()->json(['message' => $e->getMessage()], 409);
        } catch (HttpException $e) {
            return response()->json(['message' => $e->getMessage()], $e->getStatusCode());
        }

        return response()->json($step->load(['instance', 'actionedByUser']));
    }

    public function reject(RejectWorkflowStepRequest $request, WorkflowInstanceStep $workflowInstanceStep): JsonResponse
    {
        try {
            $step = $this->service->rejectStep(
                $workflowInstanceStep->id,
                (int) $request->validated()['user_id'],
                $request->validated()['comment']
            );
        } catch (\DomainException $e) {
            return response()->json(['message' => $e->getMessage()], 409);
        } catch (HttpException $e) {
            return response()->json(['message' => $e->getMessage()], $e->getStatusCode());
        }

        return response()->json($step->load(['instance', 'actionedByUser']));
    }
}
