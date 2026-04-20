<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TriggerWorkflowInstanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'event_name' => ['required', 'string', 'max:100'],
            'source_type' => ['required', 'string', 'max:255'],
            'source_id' => ['required', 'integer', 'min:1'],
            'requested_by_user_id' => ['required', 'integer', 'exists:users,id'],
        ];
    }
}
