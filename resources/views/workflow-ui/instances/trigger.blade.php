@extends('workflow-ui.layouts.app')

@section('content')
<div class="card">
    <h1>Create Event</h1>

    <div style="background:#eff6ff; border:1px solid #bfdbfe; color:#1e3a8a; padding:14px; border-radius:10px; margin-bottom:18px;">
        <strong>Demo note:</strong><br>
        For the demo, use:
        <br>• Event = <strong>Choose any request</strong>
        <br>• Booking No = <strong>BK-001</strong>
        <br>• Requested By = <strong>Beyonce - Coordinator</strong>
    </div>

    <form id="triggerForm">
        <label>Event Name</label>
        <select id="event_name">
            <option value="booking.cancellation_requested">Booking Cancellation</option>
            <option value="booking.confirmed">Booking Confirmation</option>
            <option value="unit.price_updated">Unit Price Updated</option>
        </select>

        <label>Booking No</label>
        <select id="booking_no">
            <option value="1">BK-001</option>
            <option value="2">BK-002</option>
            <option value="3">BK-003</option>
            <option value="4">BK-004</option>
            <option value="5">BK-005</option>
        </select>

        <label>Requested By</label>
        <select id="requester_user_id">
            <option value="1">Beckham - Sales Manager</option>
            <option value="2">Ronaldinho - Finance Manager</option>
            <option value="3" selected>Beyonce - Coordinator</option>
            <option value="4">Justine Bieber - Junior Sales Manager</option>
        </select>

        <button type="submit">Trigger Workflow</button>
    </form>

    <div id="triggerResult"></div>

    <div id="workflowResultCard" class="card" style="display:none; margin-top:20px;">
        <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:16px; flex-wrap:wrap;">
            <div>
                <h2 style="margin-bottom:8px;">Workflow Started Successfully</h2>
                <p class="muted" style="margin-bottom:0;">
                    The workflow instance has been created and the first approval step is now assigned.
                </p>
            </div>
            <span class="pill in_progress">in_progress</span>
        </div>

        <div class="grid" style="margin-top:18px;">
            <div>
                <label style="margin-top:0;">Instance ID</label>
                <div id="wfInstanceId" class="result"></div>
            </div>
            <div>
                <label style="margin-top:0;">Trigger Event</label>
                <div id="wfTriggerEvent" class="result"></div>
            </div>
            <div>
                <label style="margin-top:0;">Requested By</label>
                <div id="wfRequester" class="result"></div>
            </div>
            <div>
                <label style="margin-top:0;">Booking No</label>
                <div id="wfSource" class="result"></div>
            </div>
        </div>

        <div style="margin-top:20px;">
            <h3 style="margin-bottom:12px;">Approval Flow Preview</h3>
            <div id="wfStepsPreview"></div>
        </div>

        <div class="inline-actions" style="margin-top:18px;">
            <a href="{{ route('workflow.ui.inbox') }}">
                <button type="button">Go to Inbox</button>
            </a>
            <a id="viewInstanceBtn" href="#">
                <button type="button">View This Instance</button>
            </a>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function formatEventLabel(value) {
        const labels = {
            'booking.cancellation_requested': 'Booking Cancellation',
            'booking.confirmed': 'Booking Confirmed',
            'unit.price_updated': 'Unit Price Updated'
        };

        return labels[value] || value || '-';
    }

    function getRequesterName() {
        const select = document.getElementById('requester_user_id');
        return select.options[select.selectedIndex]?.text || '-';
    }

    function getBookingNoLabel() {
        const select = document.getElementById('booking_no');
        return select.options[select.selectedIndex]?.text || '-';
    }

    function getStepApproverName(step) {
        return step.assigned_user?.name
            || step.assigned_role?.name
            || step.assignedUser?.name
            || step.assignedRole?.name
            || '-';
    }

    function getStepHelperText(step) {
        if (step.assigned_role?.name || step.assignedRole?.name) {
            return 'Role-based approval';
        }

        if (step.assigned_user?.name || step.assignedUser?.name) {
            return 'User-specific approval';
        }

        return 'Approval step';
    }

    function getStatusPill(status) {
        return `<span class="pill ${status}">${status}</span>`;
    }

    function renderWorkflowSteps(steps) {
        const container = document.getElementById('wfStepsPreview');

        if (!steps || steps.length === 0) {
            container.innerHTML = `<div class="muted">No workflow steps available.</div>`;
            return;
        }

        const sortedSteps = [...steps].sort((a, b) => a.sequence - b.sequence);

        let html = `<div style="display:flex; flex-direction:column; gap:14px;">`;

        sortedSteps.forEach((step, index) => {
            html += `
                <div style="display:flex; align-items:flex-start; gap:14px;">
                    <div style="
                        min-width:42px;
                        height:42px;
                        border-radius:999px;
                        background:#dbeafe;
                        color:#1d4ed8;
                        display:flex;
                        align-items:center;
                        justify-content:center;
                        font-weight:700;
                        font-size:18px;
                        margin-top:6px;
                    ">
                        ${step.sequence}
                    </div>

                    <div style="
                        flex:1;
                        background:#f8fafc;
                        border:1px solid #e5e7eb;
                        border-radius:12px;
                        padding:14px 16px;
                    ">
                        <div style="display:flex; justify-content:space-between; gap:12px; flex-wrap:wrap; align-items:center;">
                            <div style="font-weight:700; font-size:16px;">
                                ${getStepApproverName(step)}
                            </div>
                            <div>${getStatusPill(step.status)}</div>
                        </div>

                        <div style="font-size:13px; color:#6b7280; margin-top:6px;">
                            ${getStepHelperText(step)}
                        </div>
                    </div>
                </div>
            `;

            if (index < steps.length - 1) {
                html += `
                    <div style="margin-left:18px; color:#2563eb; font-size:26px; line-height:1;">↓</div>
                `;
            }
        });

        html += `</div>`;
        container.innerHTML = html;
    }

    function renderWorkflowResult(data) {
        document.getElementById('workflowResultCard').style.display = 'block';

        document.getElementById('wfInstanceId').textContent = data.id ?? '-';
        document.getElementById('wfTriggerEvent').textContent = formatEventLabel(data.trigger_event);
        document.getElementById('wfRequester').textContent = data.requester?.name || getRequesterName();
        document.getElementById('wfSource').textContent = getBookingNoLabel();

        document.getElementById('viewInstanceBtn').href = `/workflow-ui/instances/show?id=${data.id}`;

        renderWorkflowSteps(data.steps || []);
    }

    document.getElementById('triggerForm').addEventListener('submit', async function(e) {
        e.preventDefault();

        const payload = {
            event_name: document.getElementById('event_name').value,
            source_type: 'Booking',
            source_id: parseInt(document.getElementById('booking_no').value),
            requested_by_user_id: parseInt(document.getElementById('requester_user_id').value)
        };

        try {
            const data = await apiRequest('/api/workflow-instances/trigger', 'POST', payload);
            renderWorkflowResult(data);
            renderJson('triggerResult', data);
        } catch (err) {
            document.getElementById('workflowResultCard').style.display = 'none';
            renderJson('triggerResult', err, true);
        }
    });
</script>
@endpush