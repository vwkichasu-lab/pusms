<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Pentecost University Scholarship Management System">
    <title>{{ config('app.name', 'Pentecost University Scholarship Management System') }}</title>
    <link rel="icon" href="{{ asset('images/pentvars-3d.png') }}">
    <style>
        :root {
            --navy: #082f63;
            --ink: #0f172a;
            --muted: #475569;
            --line: #cbd5e1;
            --gold: #d69e2e;
            --panel: #ffffff;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            background: #ffffff;
            color: var(--ink);
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }

        .page {
            min-height: 100vh;
            display: grid;
            grid-template-rows: auto 1fr auto;
        }

        .topbar,
        .footer {
            border-bottom: 1px solid var(--line);
            background: var(--panel);
        }

        .footer {
            border-top: 1px solid var(--line);
            border-bottom: 0;
        }

        .wrap {
            width: min(1120px, calc(100% - 32px));
            margin: 0 auto;
        }

        .topbar .wrap,
        .footer .wrap {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            padding: 16px 0;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 14px;
            min-width: 0;
        }

        .brand img {
            width: 70px;
            height: 44px;
            object-fit: contain;
            border: 1px solid var(--line);
            background: #ffffff;
        }

        .brand strong {
            display: block;
            color: var(--navy);
            font-size: 16px;
            line-height: 1.2;
        }

        .brand span {
            display: block;
            color: var(--muted);
            font-size: 13px;
            line-height: 1.3;
            margin-top: 2px;
        }

        .button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 42px;
            padding: 0 16px;
            border: 1px solid var(--navy);
            background: var(--navy);
            color: #ffffff;
            text-decoration: none;
            font-weight: 700;
            font-size: 14px;
        }

        .content {
            padding: 52px 0;
        }

        .grid {
            display: grid;
            grid-template-columns: minmax(0, 1.15fr) minmax(280px, .85fr);
            gap: 24px;
            align-items: stretch;
        }

        .panel {
            border: 1px solid var(--line);
            background: var(--panel);
            padding: 28px;
        }

        h1 {
            margin: 0;
            color: var(--navy);
            font-size: clamp(30px, 4vw, 52px);
            line-height: 1.05;
            letter-spacing: 0;
        }

        .lead {
            margin: 18px 0 0;
            max-width: 720px;
            color: var(--muted);
            font-size: 17px;
            line-height: 1.65;
        }

        .divider {
            width: 96px;
            height: 4px;
            background: var(--gold);
            margin: 26px 0;
        }

        .modules {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
            margin-top: 26px;
        }

        .module {
            border: 1px solid var(--line);
            padding: 14px;
            color: var(--ink);
            font-size: 14px;
            font-weight: 700;
            background: #ffffff;
        }

        .side img {
            width: 100%;
            height: auto;
            border: 1px solid var(--line);
            background: #ffffff;
        }

        .side dl {
            margin: 18px 0 0;
            display: grid;
            gap: 10px;
        }

        .side div {
            display: flex;
            justify-content: space-between;
            gap: 16px;
            border: 1px solid var(--line);
            padding: 12px;
            background: #ffffff;
        }

        dt {
            color: var(--muted);
            font-size: 13px;
        }

        dd {
            margin: 0;
            color: var(--navy);
            font-weight: 800;
            text-align: right;
        }

        @media (max-width: 760px) {
            .topbar .wrap,
            .footer .wrap,
            .grid {
                grid-template-columns: 1fr;
            }

            .topbar .wrap,
            .footer .wrap {
                align-items: flex-start;
            }

            .modules {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="page">
        <header class="topbar">
            <div class="wrap">
                <div class="brand">
                    <img src="{{ asset('images/pentvars-display-logo.png') }}" alt="Pentecost University logo">
                    <div>
                        <strong>Pentecost University</strong>
                        <span>Scholarship Management System</span>
                    </div>
                </div>
                <a class="button" href="{{ url('/admin') }}">Open Admin Panel</a>
            </div>
        </header>

        <main class="content">
            <div class="wrap grid">
                <section class="panel">
                    <h1>Scholarship Management and Communication System</h1>
                    <p class="lead">
                        PUSMS is the administrative system for managing scholarship students, programmes,
                        sponsors, renewals, documents, communications, reports, users, permissions, and audit trails.
                    </p>
                    <div class="divider"></div>
                    <div class="modules" aria-label="System modules">
                        <div class="module">Student Records</div>
                        <div class="module">Scholarship Assignments</div>
                        <div class="module">Renewal Workflow</div>
                        <div class="module">Email Communication</div>
                        <div class="module">Reports and Exports</div>
                        <div class="module">Roles and Audit Logs</div>
                    </div>
                </section>

                <aside class="panel side" aria-label="System summary">
                    <img src="{{ asset('images/pentvars-display-logo.png') }}" alt="Pentecost University logo">
                    <dl>
                        <div>
                            <dt>Short name</dt>
                            <dd>PUSMS</dd>
                        </div>
                        <div>
                            <dt>Framework</dt>
                            <dd>Laravel 13</dd>
                        </div>
                        <div>
                            <dt>Admin panel</dt>
                            <dd>Filament 5</dd>
                        </div>
                    </dl>
                </aside>
            </div>
        </main>

        <footer class="footer">
            <div class="wrap">
                <span>Pentecost University Scholarship Committee</span>
                <span>Secure administrative access only</span>
            </div>
        </footer>
    </div>
</body>
</html>
