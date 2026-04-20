<?php

use App\Http\Controllers\Api\ApproverInboxController;
use App\Http\Controllers\Api\WorkflowInstanceActionController;
use App\Http\Controllers\Api\WorkflowInstanceController;
use App\Http\Controllers\Api\WorkflowTemplateController;
use Illuminate\Support\Facades\Route;

Route::prefix('workflow-templates')->group(function () {
    Route::post('/', [WorkflowTemplateController::class, 'store']);
    Route::get('{workflowTemplate}', [WorkflowTemplateController::class, 'show']);
    Route::put('{workflowTemplate}', [WorkflowTemplateController::class, 'update']);
    Route::patch('{workflowTemplate}/activate', [WorkflowTemplateController::class, 'activate']);
    Route::patch('{workflowTemplate}/deactivate', [WorkflowTemplateController::class, 'deactivate']);
});

Route::prefix('workflow-instances')->group(function () {
    Route::post('trigger', [WorkflowInstanceController::class, 'trigger']);
    Route::get('{workflowInstance}', [WorkflowInstanceController::class, 'show']);
});

Route::get('approver-inbox', [ApproverInboxController::class, 'index']);

Route::prefix('workflow-instance-steps')->group(function () {
    Route::post('{workflowInstanceStep}/approve', [WorkflowInstanceActionController::class, 'approve']);
    Route::post('{workflowInstanceStep}/reject', [WorkflowInstanceActionController::class, 'reject']);
});
