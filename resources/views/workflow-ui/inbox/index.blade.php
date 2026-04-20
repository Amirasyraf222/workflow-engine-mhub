@extends('workflow-ui.layouts.app')

@section('content')
<div class="card">
    <h1>Approver Inbox</h1>
    <p class="muted">Fetch all steps currently awaiting action for a given user or role.</p>

    <div style="background:#eff6ff; border:1px solid #bfdbfe; color:#1e3a8a; padding:14px; border-radius:10px; margin-bottom:18px;">
        <strong>Demo note:</strong><br>
        Select the approver by name.
        <br><br>
        For this seeded demo:
        <br>• <strong>Beckham - Sales Manager</strong> handles Step 1
        <br>• <strong>Ronaldinho - Finance Manager</strong> handles Step 2
        <br><br>
        The UI will auto-fill the correct User ID and Role ID.
    </div>

    <div class="grid">
        <div>
            <label>Select User</label>
            <select id="userSelector" onchange="syncSelectedUser()">
                <option value="1" data-role-id="1">Beckham - Sales Manager</option>
                <option value="2" data-role-id="">Ronaldinho - Finance Manager</option>
                <option value="3" data-role-id="3">Beyonce - Coordinator</option>
                <option value="4" data-role-id="1">Justine Bieber - Junior Sales Manager</option>
            </select>
        </div>

        <div>
            <label>User ID</label>
            <input type="number" id="userId" value="1" readonly>
        </div>

        <div>
            <label>Role ID</label>
            <input type="number" id="roleId" value="1" readonly>
        </div>
    </div>

    <button onclick="loadInbox()">Load Inbox</button>
</div>

<div class="card">
    <h2>Pending Steps</h2>
    <div id="stepsTableWrap" class="muted">No data loaded yet.</div>
</div>

<div class="card">
    <h2>Action Step</h2>

    <label>Step ID</label>
    <input type="number" id="actionStepId" placeholder="Enter step ID">

    <label>Actor</label>
    <input type="text" id="actorName" value="Beckham - Sales Manager" readonly>

    <label>Actor User ID</label>
    <input type="number" id="actorUserId" value="1" readonly>

    <label>Comment</label>
    <textarea id="actionComment" placeholder="Add comment"></textarea>

    <div class="inline-actions">
        <button class="btn-success" onclick="approveStep()">Approve</button>
        <button class="btn-danger" onclick="rejectStep()">Reject</button>
    </div>

    <div id="actionResult"></div>
</div>
@endsection

@push('scripts')
<script>
    function syncSelectedUser() {
        const selector = document.getElementById('userSelector');
        const selectedOption = selector.options[selector.selectedIndex];

        const userId = selectedOption.value;
        const roleId = selectedOption.getAttribute('data-role-id') || '';
        const userName = selectedOption.text;

        document.getElementById('userId').value = userId;
        document.getElementById('roleId').value = roleId;
        document.getElementById('actorUserId').value = userId;
        document.getElementById('actorName').value = userName;
    }

    async function loadInbox() {
        const userId = document.getElementById('userId').value;
        const roleId = document.getElementById('roleId').value;

        const params = new URLSearchParams();
        if (userId) params.append('user_id', userId);
        if (roleId) params.append('role_ids[]', roleId);

        try {
            const data = await apiRequest(`/api/approver-inbox?${params.toString()}`);
            renderInboxTable(data);
        } catch (err) {
            document.getElementById('stepsTableWrap').innerHTML = `<div class="error">${JSON.stringify(err, null, 2)}</div>`;
        }
    }

    function renderInboxTable(data) {
        const rows = data.data || data;

        if (!rows || rows.length === 0) {
            document.getElementById('stepsTableWrap').innerHTML = `<div class="muted">No pending steps found.</div>`;
            return;
        }

        let html = `
            <table>
                <thead>
                    <tr>
                        <th>Step ID</th>
                        <th>Instance ID</th>
                        <th>Sequence</th>
                        <th>Status</th>
                        <th>Assigned User</th>
                        <th>Assigned Role</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
        `;

        rows.forEach(row => {
            const assignedUser =
                row.assigned_user?.name ||
                row.assignedUser?.name ||
                '-';

            const assignedRole =
                row.assigned_role?.name ||
                row.assignedRole?.name ||
                '-';

            html += `
                <tr>
                    <td>${row.id ?? ''}</td>
                    <td>${row.workflow_instance_id ?? row.instance_id ?? ''}</td>
                    <td>${row.sequence ?? ''}</td>
                    <td><span class="pill ${row.status}">${row.status ?? ''}</span></td>
                    <td>${assignedUser}</td>
                    <td>${assignedRole}</td>
                    <td>
                        <button type="button" onclick="prefillStep(${row.id})">Use This Step</button>
                    </td>
                </tr>
            `;
        });

        html += `</tbody></table>`;
        document.getElementById('stepsTableWrap').innerHTML = html;
    }

    function prefillStep(stepId) {
        document.getElementById('actionStepId').value = stepId;
        window.scrollTo({ top: document.body.scrollHeight, behavior: 'smooth' });
    }

    async function approveStep() {
        const stepId = document.getElementById('actionStepId').value;
        const userId = document.getElementById('actorUserId').value;
        const comment = document.getElementById('actionComment').value;

        if (!stepId || !userId) {
            renderJson('actionResult', { error: 'Step ID and Actor User ID are required' }, true);
            return;
        }

        try {
            const data = await apiRequest(`/api/workflow-instance-steps/${stepId}/approve`, 'POST', {
                user_id: parseInt(userId),
                comment: comment
            });
            renderJson('actionResult', data);
            loadInbox();
        } catch (err) {
            renderJson('actionResult', err, true);
        }
    }

    async function rejectStep() {
        const stepId = document.getElementById('actionStepId').value;
        const userId = document.getElementById('actorUserId').value;
        const comment = document.getElementById('actionComment').value;

        if (!stepId || !userId || !comment.trim()) {
            renderJson('actionResult', { error: 'Step ID, Actor User ID, and rejection comment are required' }, true);
            return;
        }

        try {
            const data = await apiRequest(`/api/workflow-instance-steps/${stepId}/reject`, 'POST', {
                user_id: parseInt(userId),
                comment: comment
            });
            renderJson('actionResult', data);
            loadInbox();
        } catch (err) {
            renderJson('actionResult', err, true);
        }
    }

    syncSelectedUser();
</script>
@endpush