<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreWorkflowTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'trigger_event' => ['required', 'string', 'max:100'],
            'is_active' => ['sometimes', 'boolean'],
            'steps' => ['required', 'array', 'min:1'],
            'steps.*.sequence' => ['required', 'integer', 'min:1'],
            'steps.*.approver_type' => ['required', Rule::in(['user', 'role'])],
            'steps.*.user_id' => ['nullable', 'integer', 'exists:users,id'],
            'steps.*.role_id' => ['nullable', 'integer', 'exists:roles,id'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $steps = collect($this->input('steps', []));

            if ($steps->pluck('sequence')->duplicates()->isNotEmpty()) {
                $validator->errors()->add('steps', 'Step sequence values must be unique.');
            }

            foreach ($steps as $index => $step) {
                if (($step['approver_type'] ?? null) === 'user' && empty($step['user_id'])) {
                    $validator->errors()->add("steps.$index.user_id", 'user_id is required when approver_type is user.');
                }

                if (($step['approver_type'] ?? null) === 'role' && empty($step['role_id'])) {
                    $validator->errors()->add("steps.$index.role_id", 'role_id is required when approver_type is role.');
                }
            }
        });
    }
}
