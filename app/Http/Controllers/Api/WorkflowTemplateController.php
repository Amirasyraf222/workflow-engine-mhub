<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreWorkflowTemplateRequest;
use App\Http\Requests\UpdateWorkflowTemplateRequest;
use App\Models\WorkflowTemplate;
use App\Services\WorkflowTemplateService;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class WorkflowTemplateController extends Controller
{
    public function __construct(private WorkflowTemplateService $service)
    {
    }

    public function store(StoreWorkflowTemplateRequest $request): JsonResponse
    {
        try {
            $template = $this->service->create($request->validated());
        } catch (\DomainException $e) {
            throw ValidationException::withMessages(['trigger_event' => $e->getMessage()]);
        }

        return response()->json($template->load(['steps.user', 'steps.role']), 201);
    }

    public function show(WorkflowTemplate $workflowTemplate): JsonResponse
    {
        return response()->json($workflowTemplate->load(['steps.user', 'steps.role']));
    }

    public function update(UpdateWorkflowTemplateRequest $request, WorkflowTemplate $workflowTemplate): JsonResponse
    {
        try {
            $template = $this->service->update($workflowTemplate, $request->validated());
        } catch (\DomainException $e) {
            return response()->json(['message' => $e->getMessage()], 409);
        }

        return response()->json($template->load(['steps.user', 'steps.role']));
    }

    public function activate(WorkflowTemplate $workflowTemplate): JsonResponse
    {
        try {
            $template = $this->service->setActive($workflowTemplate, true);
        } catch (\DomainException $e) {
            return response()->json(['message' => $e->getMessage()], 409);
        }

        return response()->json($template);
    }

    public function deactivate(WorkflowTemplate $workflowTemplate): JsonResponse
    {
        $template = $this->service->setActive($workflowTemplate, false);

        return response()->json($template);
    }
}
