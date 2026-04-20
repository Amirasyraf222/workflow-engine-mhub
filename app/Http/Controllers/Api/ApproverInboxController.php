<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\WorkflowInstanceStep;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApproverInboxController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        $user = User::with('roles')->findOrFail($validated['user_id']);
        $roleIds = $user->roles->pluck('id')->all();

        $steps = WorkflowInstanceStep::query()
            ->with(['instance', 'assignedUser', 'assignedRole'])
            ->where('status', 'awaiting_action')
            ->where(function ($query) use ($validated, $roleIds) {
                $query->where('assigned_user_id', $validated['user_id']);

                if (!empty($roleIds)) {
                    $query->orWhereIn('assigned_role_id', $roleIds);
                }
            })
            ->orderBy('id')
            ->get();

        return response()->json($steps);
    }
}