<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WorkflowUiController;


Route::get('/', function () {
    return redirect('/workflow-ui');
});

Route::prefix('workflow-ui')->group(function () {
    Route::view('/', 'workflow-ui.dashboard')->name('workflow.ui.dashboard');

    Route::get('/templates/create', [WorkflowUiController::class, 'createTemplatePage'])
        ->name('workflow.ui.templates.create');

    Route::view('/templates/show', 'workflow-ui.templates.show')->name('workflow.ui.templates.show');
    Route::get('/instances/trigger', [WorkflowUiController::class, 'triggerPage'])->name('workflow.ui.instances.trigger');
    Route::view('/instances/show', 'workflow-ui.instances.show')->name('workflow.ui.instances.show');
    Route::view('/approver-inbox', 'workflow-ui.inbox.index')->name('workflow.ui.inbox');
});