<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkflowTemplate extends Model
{
    protected $fillable = [
        'name',
        'description',
        'trigger_event',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function steps(): HasMany
    {
        return $this->hasMany(WorkflowTemplateStep::class)->orderBy('sequence');
    }
}
