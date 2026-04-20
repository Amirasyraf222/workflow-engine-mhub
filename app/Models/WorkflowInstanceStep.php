<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkflowInstanceStep extends Model
{
    protected $fillable = [
        'workflow_instance_id',
        'sequence',
        'status',
        'assigned_user_id',
        'assigned_role_id',
        'actioned_by',
        'decision',
        'comment',
        'actioned_at',
    ];

    protected $casts = [
        'actioned_at' => 'datetime',
    ];

    public function instance(): BelongsTo
    {
        return $this->belongsTo(WorkflowInstance::class, 'workflow_instance_id');
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    public function assignedRole(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'assigned_role_id');
    }

    public function actionedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actioned_by');
    }

    public function actions(): HasMany
    {
        return $this->hasMany(WorkflowStepAction::class, 'workflow_instance_step_id');
    }
}
