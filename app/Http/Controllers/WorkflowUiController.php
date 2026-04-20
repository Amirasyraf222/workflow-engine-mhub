<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Role;
use App\Models\User;
use Illuminate\View\View;

class WorkflowUiController extends Controller
{
    public function createTemplatePage(): View
    {
        $events = [
            [
                'value' => 'booking.cancellation_requested',
                'label' => 'Booking Cancellation',
            ],
            [
                'value' => 'booking.confirmed',
                'label' => 'Booking Confirmation',
            ],
            [
                'value' => 'unit.price_updated',
                'label' => 'Unit Price Update',
            ],
        ];

        $roles = Role::query()
            ->select('id', 'name', 'code')
            ->orderBy('name')
            ->get();

        $users = User::query()
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        return view('workflow-ui.templates.create', [
            'events' => $events,
            'roles' => $roles,
            'users' => $users,
        ]);
    }

    public function triggerPage(): View
    {
        $events = [
            [
                'value' => 'booking.cancellation_requested',
                'label' => 'Booking Cancellation',
            ],
            [
                'value' => 'booking.confirmed',
                'label' => 'Booking Confirmed',
            ],
            [
                'value' => 'unit.price_updated',
                'label' => 'Unit Price Updated',
            ],
        ];

        $bookings = Booking::query()
            ->select('id', 'booking_no', 'buyer_name', 'status')
            ->orderBy('id')
            ->get();

        $users = User::query()
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        return view('workflow-ui.instances.trigger', [
            'events' => $events,
            'bookings' => $bookings,
            'users' => $users,
        ]);
    }
}