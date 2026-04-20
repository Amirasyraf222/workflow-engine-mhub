@extends('workflow-ui.layouts.app')

@section('content')
<div class="card">
    <h1>View Workflow Template</h1>
    <p class="muted">Load a template and view its approval flow visually.</p>

    <div class="grid">
        <div>
            <label>Template ID</label>
            <input type="number" id="templateId" placeholder="Enter template ID">
        </div>
    </div>

    <div class="inline-actions">
        <button type="button" onclick="loadTemplate()">Show Workflow</button>
    </div>
</div>

<div id="templateVisualWrap" style="display:none;">
    <div class="card">
        <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:16px; flex-wrap:wrap;">
            <div>
                <h2 id="templateName" style="margin-bottom:8px;"></h2>
                <p id="templateDescription" class="muted" style="margin-bottom:10px;"></p>
                <p><strong>Trigger Event:</strong> <span id="templateTrigger"></span></p>
            </div>
            <div>
                <span id="templateStatusBadge" class="pill"></span>
            </div>
        </div>
    </div>

    <div class="card">
        <h2>Approval Workflow</h2>
        <div id="workflowStepsVisual"></div>
    </div>
</div>

<div id="templateResult"></div>
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

    function getApproverLabel(step) {
        const roleName = step.role?.name || step.assigned_role?.name || step.assignedRole?.name;
        const userName = step.user?.name || step.assigned_user?.name || step.assignedUser?.name;

        if (step.approver_type === 'role') {
            return roleName || 'Role approver';        }

        if (step.approver_type === 'user') {
            return userName || 'User approver';        }

        return '-';
    }

    function renderWorkflowSteps(steps) {
        const container = document.getElementById('workflowStepsVisual');

        if (!steps || steps.length === 0) {
            container.innerHTML = `<div class="muted">No steps found.</div>`;
            return;
        }

        const sortedSteps = [...steps].sort((a, b) => a.sequence - b.sequence);

        let html = `
            <div style="display:flex; flex-direction:column; gap:16px; margin-top:16px;">
        `;

        sortedSteps.forEach((step, index) => {
            html += `
                <div style="display:flex; align-items:center; gap:16px; flex-wrap:wrap;">
                    <div style="
                        min-width:48px; height:48px; border-radius:999px;
                        background:#dbeafe; color:#1d4ed8;
                        display:flex; align-items:center; justify-content:center;
                        font-weight:700; font-size:18px;
                    ">
                        ${step.sequence}
                    </div>

                    <div style="
                        flex:1;
                        background:#f8fafc;
                        border:1px solid #e5e7eb;
                        border-radius:12px;
                        padding:16px;
                    ">
                        <div style="font-weight:700; margin-bottom:8px;">Step ${step.sequence}</div>
                       <div style="font-weight:600; font-size:15px;">
                            ${getApproverLabel(step)}
                        </div>
                        <div style="font-size:13px; color:#6b7280;">
                            ${step.approver_type === 'role' ? 'Role-based approval' : 'User-specific approval'}
                        </div>
                    </div>
                </div>
            `;

            if (index < sortedSteps.length - 1) {
                html += `
                    <div style="margin-left:22px; color:#2563eb; font-size:28px; line-height:1;">↓</div>
                `;
            }
        });

        html += `</div>`;
        container.innerHTML = html;
    }

    function renderTemplateVisual(data) {
        document.getElementById('templateName').textContent = data.name || '-';
        document.getElementById('templateDescription').textContent = data.description || '-';
        document.getElementById('templateTrigger').textContent = formatTriggerEvent(data.trigger_event);

        const badge = document.getElementById('templateStatusBadge');
        badge.textContent = data.is_active ? 'Active' : 'Inactive';
        badge.className = data.is_active ? 'pill approved' : 'pill pending';

        renderWorkflowSteps(data.steps || []);
        document.getElementById('templateVisualWrap').style.display = 'block';
    }

    async function loadTemplate() {
        const id = document.getElementById('templateId').value;

        if (!id) {
            renderJson('templateResult', { error: 'Template ID is required' }, true);
            return;
        }

        try {
            const data = await apiRequest(`/api/workflow-templates/${id}`);
            renderTemplateVisual(data);
            renderJson('templateResult', data);
        } catch (err) {
            document.getElementById('templateVisualWrap').style.display = 'none';
            renderJson('templateResult', err, true);
        }
    }

    async function activateTemplate() {
        const id = document.getElementById('templateId').value;

        if (!id) {
            renderJson('templateResult', { error: 'Template ID is required' }, true);
            return;
        }

        try {
            const data = await apiRequest(`/api/workflow-templates/${id}/activate`, 'PATCH');
            renderJson('templateResult', data);

            if (typeof loadTemplate === 'function') {
                await loadTemplate();
            }
        } catch (err) {
            renderJson('templateResult', err, true);
        }
    }

    async function deactivateTemplate() {
        const id = document.getElementById('templateId').value;

        if (!id) {
            renderJson('templateResult', { error: 'Template ID is required' }, true);
            return;
        }

        try {
            const data = await apiRequest(`/api/workflow-templates/${id}/deactivate`, 'PATCH');
            renderJson('templateResult', data);

            if (typeof loadTemplate === 'function') {
                await loadTemplate();
            }
        } catch (err) {
            renderJson('templateResult', err, true);
        }
    }

    (function autoLoadFromQuery() {
        const params = new URLSearchParams(window.location.search);
        const id = params.get('id');

        if (id) {
            document.getElementById('templateId').value = id;
            loadTemplate();
        }
    })();
</script>
@endpush