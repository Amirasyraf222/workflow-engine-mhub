@extends('workflow-ui.layouts.app')

@section('content')
<div class="card">
    <h1>Workflow Engine Demo UI</h1>
    <p class="muted">
        This Blade frontend sits on top of the API backend so the examiner can test the workflow engine without using Postman.
    </p>

    <div style="background:#eff6ff; border:1px solid #bfdbfe; color:#1e3a8a; padding:16px; border-radius:12px; margin-top:16px;">
        <strong>Seeded demo users:</strong>
        <br>• Beckham - Sales Manager
        <br>• Ronaldinho - Finance Manager
        <br>• Beyonce - Coordinator
        <br>• Justine Bieber - Junior Sales Manager
        <br><br>
        <strong>Recommended flow:</strong>
        <br>1. Setup Workflow
        <br>2. View Workflow
        <br>3. Trigger Workflow
        <br>4. Approver Inbox
        <br>5. View Instance
    </div>
</div>

<style>
    .flow-grid {
        display: grid;
        grid-template-columns: 1fr 70px 1fr 70px 1fr;
        gap: 16px;
        align-items: stretch;
        margin-top: 28px;
    }

    .flow-grid-bottom {
        display: grid;
        grid-template-columns: 1fr 70px 1fr;
        gap: 16px;
        align-items: stretch;
        margin-top: 18px;
    }

    .flow-card {
        background: #fff;
        border-radius: 18px;
        padding: 22px;
        box-shadow: 0 6px 18px rgba(0,0,0,0.08);
        min-height: 200px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        border: 1px solid #eef2f7;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .flow-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 24px rgba(0,0,0,0.10);
    }

    .flow-card h2 {
        margin: 0 0 10px 0;
        font-size: 20px;
        color: #0f172a;
    }

    .flow-card p {
        margin: 0 0 18px 0;
        color: #475569;
        line-height: 1.5;
    }

    .flow-card button {
        width: 100%;
        border-radius: 10px;
        font-weight: 700;
    }

    .flow-arrow {
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 34px;
        font-weight: bold;
        color: #2563eb;
        min-height: 200px;
    }

    .flow-down-wrap {
        display: flex;
        justify-content: center;
        align-items: center;
        margin: 10px 0 4px 0;
    }

    .flow-down {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 56px;
        height: 56px;
        border-radius: 999px;
        background: #eff6ff;
        color: #2563eb;
        font-size: 32px;
        font-weight: bold;
        border: 1px solid #bfdbfe;
        box-shadow: 0 4px 10px rgba(37, 99, 235, 0.12);
    }

    .flow-step-badge {
        display: inline-block;
        margin-bottom: 12px;
        padding: 6px 12px;
        border-radius: 999px;
        background: #dbeafe;
        color: #1d4ed8;
        font-size: 12px;
        font-weight: 700;
        letter-spacing: 0.3px;
    }

    @media (max-width: 1100px) {
        .flow-grid,
        .flow-grid-bottom {
            grid-template-columns: 1fr;
        }

        .flow-arrow {
            min-height: auto;
            transform: rotate(90deg);
            font-size: 28px;
        }

        .flow-down {
            transform: none;
        }
    }
</style>

<div class="flow-grid">
    <div class="flow-card">
        <div>
            <span class="flow-step-badge">STEP 1</span>
            <h2>1. Setup Workflow</h2>
            <p>Create a workflow template with approvers by role or user name.</p>
        </div>
        <a href="{{ route('workflow.ui.templates.create') }}">
            <button>Go to Create Template</button>
        </a>
    </div>

    <div class="flow-arrow">→</div>

    <div class="flow-card">
        <div>
            <span class="flow-step-badge">STEP 2</span>
            <h2>2. View Workflow</h2>
            <p>Fetch an existing workflow template by template ID.</p>
        </div>
        <a href="{{ route('workflow.ui.templates.show') }}">
            <button>Go to View Template</button>
        </a>
    </div>

    <div class="flow-arrow">→</div>

    <div class="flow-card">
        <div>
            <span class="flow-step-badge">STEP 3</span>
            <h2>3. Trigger Workflow</h2>
            <p>Trigger an instance using event name, entity type, entity ID, and requester name.</p>
        </div>
        <a href="{{ route('workflow.ui.instances.trigger') }}">
            <button>Go to Trigger Workflow</button>
        </a>
    </div>
</div>

<div class="flow-down-wrap">
    <div class="flow-down">↓</div>
</div>

<div class="flow-grid-bottom">
    <div class="flow-card">
        <div>
            <span class="flow-step-badge">STEP 4</span>
            <h2>4. Approver Inbox</h2>
            <p>Select approver by name and action pending steps directly from browser.</p>
        </div>
        <a href="{{ route('workflow.ui.inbox') }}">
            <button>Go to Inbox</button>
        </a>
    </div>

    <div class="flow-arrow">→</div>

    <div class="flow-card">
        <div>
            <span class="flow-step-badge">STEP 5</span>
            <h2>5. View Instance</h2>
            <p>Check the live state and history of a workflow instance.</p>
        </div>
        <a href="{{ route('workflow.ui.instances.show') }}">
            <button>Go to View Instance</button>
        </a>
    </div>
</div>
@endsection