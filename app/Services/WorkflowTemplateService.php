<?php

namespace App\Services;

use App\Models\WorkflowTemplate;
use Illuminate\Support\Facades\DB;

class WorkflowTemplateService
{
    public function create(array $data): WorkflowTemplate
    {
        if (($data['is_active'] ?? false) && $this->activeTemplateExists($data['trigger_event'])) {
            throw new \DomainException('This trigger event is already bound to another active template.');
        }

        return DB::transaction(function () use ($data) {
            $template = WorkflowTemplate::create([
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'trigger_event' => $data['trigger_event'],
                'is_active' => (bool) ($data['is_active'] ?? false),
            ]);

            foreach (collect($data['steps'])->sortBy('sequence') as $step) {
                $template->steps()->create([
                    'sequence' => $step['sequence'],
                    'approver_type' => $step['approver_type'],
                    'user_id' => $step['approver_type'] === 'user' ? $step['user_id'] : null,
                    'role_id' => $step['approver_type'] === 'role' ? $step['role_id'] : null,
                ]);
            }

            return $template;
        });
    }

    public function update(WorkflowTemplate $template, array $data): WorkflowTemplate
    {
        $running = DB::table('workflow_instances')
            ->where('workflow_template_id', $template->id)
            ->whereIn('status', ['pending', 'in_progress'])
            ->exists();

        if ($running) {
            throw new \DomainException('Template cannot be updated while instances are running.');
        }

        $targetEvent = $data['trigger_event'];
        $targetActive = (bool) ($data['is_active'] ?? $template->is_active);

        if ($targetActive && $this->activeTemplateExists($targetEvent, $template->id)) {
            throw new \DomainException('This trigger event is already bound to another active template.');
        }

        return DB::transaction(function () use ($template, $data, $targetActive) {
            $template->update([
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'trigger_event' => $data['trigger_event'],
                'is_active' => $targetActive,
            ]);

            $template->steps()->delete();

            foreach (collect($data['steps'])->sortBy('sequence') as $step) {
                $template->steps()->create([
                    'sequence' => $step['sequence'],
                    'approver_type' => $step['approver_type'],
                    'user_id' => $step['approver_type'] === 'user' ? $step['user_id'] : null,
                    'role_id' => $step['approver_type'] === 'role' ? $step['role_id'] : null,
                ]);
            }

            return $template->fresh();
        });
    }

    public function setActive(WorkflowTemplate $template, bool $active): WorkflowTemplate
    {
        if ($active && $this->activeTemplateExists($template->trigger_event, $template->id)) {
            throw new \DomainException('Another active template already exists for this trigger event.');
        }

        $template->update(['is_active' => $active]);

        return $template->fresh();
    }

    private function activeTemplateExists(string $triggerEvent, ?int $ignoreId = null): bool
    {
        return WorkflowTemplate::query()
            ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->where('trigger_event', $triggerEvent)
            ->where('is_active', true)
            ->exists();
    }
}
