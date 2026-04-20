<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Workflow Engine UI</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        * {
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            margin: 0;
            background: #f5f7fb;
            color: #1f2937;
        }

        .container {
            max-width: 1100px;
            margin: 0 auto;
            padding: 24px;
        }

        .navbar {
            background: #111827;
            padding: 14px 24px;
        }

        .navbar a {
            color: #fff;
            margin-right: 18px;
            text-decoration: none;
            font-weight: 600;
        }

        .navbar a:hover {
            text-decoration: underline;
        }

        .card {
            background: #fff;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 4px 14px rgba(0,0,0,0.08);
            margin-bottom: 20px;
        }

        h1, h2, h3 {
            margin-top: 0;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 20px;
        }

        label {
            display: block;
            font-weight: 600;
            margin-bottom: 6px;
            margin-top: 14px;
        }

        input, select, textarea, button {
            width: 100%;
            padding: 10px 12px;
            border-radius: 8px;
            border: 1px solid #d1d5db;
            font-size: 14px;
        }

        textarea {
            min-height: 100px;
            resize: vertical;
        }

        button {
            background: #2563eb;
            color: white;
            border: none;
            cursor: pointer;
            font-weight: 600;
            margin-top: 16px;
        }

        button:hover {
            background: #1d4ed8;
        }

        .btn-secondary {
            background: #6b7280;
        }

        .btn-danger {
            background: #dc2626;
        }

        .btn-success {
            background: #16a34a;
        }

        .inline-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .inline-actions button {
            width: auto;
            min-width: 120px;
        }

        .result, .error {
            white-space: pre-wrap;
            padding: 14px;
            border-radius: 8px;
            margin-top: 16px;
            font-size: 14px;
        }

        .result {
            background: #ecfdf5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }

        .error {
            background: #fef2f2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }

        .step-row {
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            padding: 16px;
            margin-top: 14px;
            background: #f9fafb;
        }

        .muted {
            color: #6b7280;
            font-size: 14px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 12px;
        }

        th, td {
            border: 1px solid #e5e7eb;
            padding: 10px;
            text-align: left;
            vertical-align: top;
        }

        th {
            background: #f3f4f6;
        }

        .pill {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 700;
            background: #e5e7eb;
        }

        .pill.pending { background: #fef3c7; color: #92400e; }
        .pill.approved { background: #dcfce7; color: #166534; }
        .pill.rejected { background: #fee2e2; color: #991b1b; }
        .pill.awaiting_action { background: #dbeafe; color: #1d4ed8; }
        .pill.in_progress { background: #e0e7ff; color: #4338ca; }

        code {
            background: #f3f4f6;
            padding: 2px 6px;
            border-radius: 6px;
        }
    </style>
</head>
<body>
    @include('workflow-ui.partials.nav')

    <div class="container">
        @yield('content')
    </div>

    <script>
        async function apiRequest(url, method = 'GET', payload = null) {
            const options = {
                method,
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            };

            if (payload) {
                options.body = JSON.stringify(payload);
            }

            const response = await fetch(url, options);
            const data = await response.json().catch(() => ({}));

            if (!response.ok) {
                throw {
                    status: response.status,
                    data
                };
            }

            return data;
        }

        function renderJson(elementId, data, isError = false) {
            const el = document.getElementById(elementId);
            if (!el) return;

            el.className = isError ? 'error' : 'result';
            el.textContent = JSON.stringify(data, null, 2);
        }

        function clearResult(elementId) {
            const el = document.getElementById(elementId);
            if (!el) return;
            el.className = '';
            el.textContent = '';
        }
    </script>

    @stack('scripts')
</body>
</html>