@extends('workflow-ui.layouts.app')

@section('content')
<div class="card">
    <h1>View Event Instance</h1>
    <p class="muted">Load a workflow instance and view its progress visually.</p>

    <label>Event ID</label>
    <input type="number" id="instanceId" placeholder="Enter instance ID">

    <button onclick="loadInstance()">Load Instance</button>
</div>

<div id="instanceVisualWrap" style="display:none;">
    <div class="card">
        <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:16px; flex-wrap:wrap;">
            <div>
                <h2 id="instanceTitle" style="margin-bottom:8px;"></h2>
                <p id="instanceSubtitle" class="muted" style="margin-bottom:10px;"></p>
                <p><strong>Triggered Event:</strong> <span id="instanceTrigger"></span></p>
                <p><strong>Requested By:</strong> <span id="instanceRequester"></span></p>
                <p><strong>Source:</strong> <span id="instanceSource"></span></p>
            </div>
            <div>
                <span id="instanceStatusBadge" class="pill"></span>
            </div>
        </div>
    </div>

    <div class="card">
        <h2>Approval Progress</h2>
        <div id="instanceStepsVisual"></div>
    </div>
</div>

<div id="instanceResult"></div>
@endsection

@push('scripts')
<script>
    function formatTriggerEvent(value) {
        const labels = {
            'booking.cancellation_requested': 'Booking Cancellation Requested',
            'booking.confirmed': 'Booking Confirmed',
            'unit.price_updated': 'Unit Price Updated'
        };

        return labels[value] || value || '-';
    }

    function getStepAssignee(step) {
        return step.assigned_user?.name
            || step.assigned_role?.name
            || step.assignedUser?.name
            || step.assignedRole?.name
            || '-';
    }

    function getActionedBy(step) {
        return step.actioned_by_user?.name
            || step.actionedByUser?.name
            || '-';
    }

    function getStepHint(step) {
        if (step.assigned_role?.name || step.assignedRole?.name) {
            return 'Role-based approval';
        }

        if (step.assigned_user?.name || step.assignedUser?.name) {
            return 'User-specific approval';
        }

        return 'Approval step';
    }

    function renderInstanceSteps(steps) {
        const container = document.getElementById('instanceStepsVisual');

        if (!steps || steps.length === 0) {
            container.innerHTML = `<div class="muted">No steps found.</div>`;
            return;
        }

        const sortedSteps = [...steps].sort((a, b) => a.sequence - b.sequence);

        let html = `<div style="display:flex; flex-direction:column; gap:14px; margin-top:16px;">`;

        sortedSteps.forEach((step, index) => {
            let commentHtml = '';
            if (step.comment) {
                commentHtml = `
                    <div style="margin-top:8px; font-size:13px; color:#475569;">
                        <strong>Comment:</strong> ${step.comment}
                    </div>
                `;
            }

            let actedByHtml = '';
            if (step.actioned_by || step.actioned_by_user || step.actionedByUser) {
                actedByHtml = `
                    <div style="margin-top:8px; font-size:13px; color:#475569;">
                        <strong>Actioned By:</strong> ${getActionedBy(step)}
                    </div>
                `;
            }

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
                            <div>
                                <div style="font-weight:700; font-size:16px;">${getStepAssignee(step)}</div>
                                <div style="font-size:13px; color:#6b7280; margin-top:4px;">
                                    ${getStepHint(step)}
                                </div>
                            </div>
                            <div>
                                <span class="pill ${step.status}">${step.status}</span>
                            </div>
                        </div>

                        ${actedByHtml}
                        ${commentHtml}
                    </div>
                </div>
            `;

            if (index < sortedSteps.length - 1) {
                html += `
                    <div style="margin-left:18px; color:#2563eb; font-size:26px; line-height:1;">↓</div>
                `;
            }
        });

        html += `</div>`;
        container.innerHTML = html;
    }

    function renderInstanceVisual(data) {
        document.getElementById('instanceTitle').textContent = `Workflow Instance #${data.id}`;
        document.getElementById('instanceSubtitle').textContent = data.template?.name || 'Workflow instance details';
        document.getElementById('instanceTrigger').textContent = formatTriggerEvent(data.trigger_event);
        document.getElementById('instanceRequester').textContent = data.requester?.name || '-';
        document.getElementById('instanceSource').textContent = `${data.source_type ?? '-'} #${data.source_id ?? '-'}`;

        const badge = document.getElementById('instanceStatusBadge');
        badge.textContent = data.status || '-';
        badge.className = `pill ${data.status || ''}`;

        renderInstanceSteps(data.steps || []);
        document.getElementById('instanceVisualWrap').style.display = 'block';
    }

    async function loadInstance() {
        const id = document.getElementById('instanceId').value;

        if (!id) {
            renderJson('instanceResult', { error: 'Instance ID is required' }, true);
            return;
        }

        try {
            const data = await apiRequest(`/api/workflow-instances/${id}`);
            renderInstanceVisual(data);
            renderJson('instanceResult', data);
        } catch (err) {
            document.getElementById('instanceVisualWrap').style.display = 'none';
            renderJson('instanceResult', err, true);
        }
    }

    (function autoLoadFromQuery() {
        const params = new URLSearchParams(window.location.search);
        const id = params.get('id');

        if (id) {
            document.getElementById('instanceId').value = id;
            loadInstance();
        }
    })();
</script>
@endpush