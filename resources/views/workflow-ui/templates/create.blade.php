@extends('workflow-ui.layouts.app')

@section('content')
<div class="card">
    <h1>Create Workflow Template</h1>

    <div style="background:#eff6ff; border:1px solid #bfdbfe; color:#1e3a8a; padding:14px; border-radius:10px; margin-bottom:18px;">
        <strong>Demo note:</strong><br>
        Use this seeded flow for the easiest demo:
        <br>• Step 1 = <strong>Sales Manager</strong>
        <br>• Step 2 = <strong>Farid Finance Manager</strong>
    </div>

    <form id="templateForm">
        <label>Template Name</label>
        <input type="text" id="name" value="Booking Cancellation Approval" required>

        <label>Description</label>
        <textarea id="description">Approval flow for booking cancellation requests.</textarea>

        <label>Action</label>
        <select id="trigger_event">
            @foreach ($events as $event)
                <option value="{{ $event['value'] }}">
                    {{ $event['label'] }}
                </option>
            @endforeach
        </select>

        <label>Is Active</label>
        <select id="is_active">
            <option value="1">Yes</option>
            <option value="0">No</option>
        </select>

        <div style="margin-top: 28px; margin-bottom: 12px;">
            <div style="display: flex; align-items: center; gap: 12px;">
                <div style="flex:1; height:1px; background:#e5e7eb;"></div>
                <span style="font-size: 13px; font-weight: 700; color: #6b7280; letter-spacing: 0.5px;">
                    APPROVAL FLOW CONFIGURATION
                </span>
                <div style="flex:1; height:1px; background:#e5e7eb;"></div>
            </div>
        </div>

        <h3 style="margin-top:24px;">Approver Settings</h3>
        <div id="stepsContainer"></div>

        <div class="inline-actions">
            <button type="button" class="btn-secondary" onclick="addStep()">Add Step</button>
            <button type="submit">Create Template</button>
        </div>
    </form>

    <div id="resultBox"></div>

    <div id="templateSuccessCard" style="display:none; margin-top:16px;" class="card">
        <h2 style="margin-bottom:12px;">Template Created Successfully</h2>
        <p><strong>Template ID:</strong> <span id="createdTemplateId"></span></p>
        <div class="inline-actions">
            <a id="viewCreatedTemplateBtn" href="#">
                <button type="button">View This Template</button>
            </a>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let stepCount = 0;

    const roles = @json($roles->map(fn($role) => [
        'id' => $role->id,
        'name' => $role->name,
    ]));

    const users = @json($users->map(fn($user) => [
        'id' => $user->id,
        'name' => $user->name,
    ]));

    function buildRoleOptions(selectedId = '') {
        let html = `<option value="">Select Role</option>`;

        roles.forEach(role => {
            html += `<option value="${role.id}" ${String(selectedId) === String(role.id) ? 'selected' : ''}>${role.name}</option>`;
        });

        return html;
    }

    function buildUserOptions(selectedId = '') {
        let html = `<option value="">Select User</option>`;

        users.forEach(user => {
            html += `<option value="${user.id}" ${String(selectedId) === String(user.id) ? 'selected' : ''}>${user.name}</option>`;
        });

        return html;
    }

    function toggleStepFields(row) {
        const approverType = row.querySelector('.step-approver-type').value;
        const roleWrap = row.querySelector('.role-wrap');
        const userWrap = row.querySelector('.user-wrap');

        if (approverType === 'role') {
            roleWrap.style.display = 'block';
            userWrap.style.display = 'none';
            row.querySelector('.step-user-id').value = '';
        } else {
            roleWrap.style.display = 'none';
            userWrap.style.display = 'block';
            row.querySelector('.step-role-id').value = '';
        }
    }

    function addStep(step = {}) {
        stepCount++;

        const container = document.getElementById('stepsContainer');
        const div = document.createElement('div');
        div.className = 'step-row';

        div.innerHTML = `
            <label>Approver Sequence</label>
            <input type="number" class="step-sequence" value="${step.sequence ?? stepCount}" required>

            <label>Approver Type</label>
            <select class="step-approver-type" onchange="toggleStepFields(this.closest('.step-row'))">
                <option value="role" ${step.approver_type === 'role' ? 'selected' : ''}>Role</option>
                <option value="user" ${step.approver_type === 'user' ? 'selected' : ''}>User</option>
            </select>

            <div class="role-wrap">
                <label>Approver Role</label>
                <select class="step-role-id">
                    ${buildRoleOptions(step.role_id ?? '')}
                </select>
            </div>

            <div class="user-wrap">
                <label>User</label>
                <select class="step-user-id">
                    ${buildUserOptions(step.user_id ?? '')}
                </select>
            </div>

            <button type="button" class="btn-danger" onclick="this.parentElement.remove()">Remove Step</button>
        `;

        container.appendChild(div);
        toggleStepFields(div);
    }

    const salesManagerRole = roles.find(role => role.name === 'Sales Manager');
    const faridUser = users.find(user => user.name === 'Farid Finance Manager');

    addStep({
        sequence: 1,
        approver_type: 'role',
        role_id: salesManagerRole ? salesManagerRole.id : ''
    });

    addStep({
        sequence: 2,
        approver_type: 'user',
        user_id: faridUser ? faridUser.id : ''
    });

    document.getElementById('templateForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        clearResult('resultBox');

        const steps = [...document.querySelectorAll('.step-row')].map(row => {
            const approverType = row.querySelector('.step-approver-type').value;
            const roleId = row.querySelector('.step-role-id').value;
            const userId = row.querySelector('.step-user-id').value;

            return {
                sequence: parseInt(row.querySelector('.step-sequence').value),
                approver_type: approverType,
                role_id: approverType === 'role' && roleId ? parseInt(roleId) : null,
                user_id: approverType === 'user' && userId ? parseInt(userId) : null
            };
        });

        const payload = {
            name: document.getElementById('name').value,
            description: document.getElementById('description').value,
            trigger_event: document.getElementById('trigger_event').value,
            is_active: parseInt(document.getElementById('is_active').value),
            steps
        };

        try {
            const data = await apiRequest('/api/workflow-templates', 'POST', payload);

            renderJson('resultBox', data);

            if (data.id) {
                document.getElementById('createdTemplateId').textContent = data.id;
                document.getElementById('viewCreatedTemplateBtn').href = `/workflow-ui/templates/show?id=${data.id}`;
                document.getElementById('templateSuccessCard').style.display = 'block';
            }
        } catch (err) {
            document.getElementById('templateSuccessCard').style.display = 'none';
            renderJson('resultBox', err, true);
        }
    });
</script>
@endpush