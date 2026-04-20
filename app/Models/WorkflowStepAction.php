<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkflowStepAction extends Model
{
    protected $fillable = [
        'workflow_instance_step_id',
        'workflow_instance_id',
        'acted_by',
        'decision',
        'comment',
        'acted_at',
    ];

    protected $casts = [
        'acted_at' => 'datetime',
    ];

    public function step(): BelongsTo
    {
        return $this->belongsTo(WorkflowInstanceStep::class, 'workflow_instance_step_id');
    }

    public function instance(): BelongsTo
    {
        return $this->belongsTo(WorkflowInstance::class, 'workflow_instance_id');
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'acted_by');
    }
}
