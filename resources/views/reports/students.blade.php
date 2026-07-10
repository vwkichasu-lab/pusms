<!DOCTYPE html>
<html>
<head>
    <title>PUSMS Student Report</title>
    <link rel="icon" href="{{ asset('images/pentvars-3d.png') }}">
    <style>
        body { background: #fff; color: #0f172a; font-family: Arial, sans-serif; margin: 24px; }
        header { display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #cbd5e1; padding-bottom: 12px; }
        img { width: 150px; height: auto; }
        h1 { color: #082f63; margin: 0; }
        .actions a, button { border: 1px solid #082f63; background: #fff; color: #082f63; padding: 8px 10px; text-decoration: none; font-weight: 700; margin-left: 6px; }
        table { width: 100%; border-collapse: collapse; margin-top: 18px; font-size: 12px; table-layout: fixed; }
        th, td { border: 1px solid #cbd5e1; padding: 9px; text-align: left; vertical-align: top; overflow-wrap: anywhere; line-height: 1.45; }
        th { background: #eff6ff; color: #082f63; }
        th:nth-child(1), td:nth-child(1) { width: 20%; }
        th:nth-child(2), td:nth-child(2) { width: 28%; }
        th:nth-child(3), td:nth-child(3) { width: 14%; }
        th:nth-child(4), td:nth-child(4) { width: 24%; }
        th:nth-child(5), td:nth-child(5) { width: 14%; }
        @media print { .actions { display: none; } body { margin: 0; } }
    </style>
</head>
<body>
<header>
    <div>
        <h1>Pentecost University Scholarship Student Report</h1>
        <p>Generated {{ $generatedAt->format('Y-m-d H:i') }} | {{ $rows->count() }} record(s)</p>
    </div>
    <img src="{{ asset('images/pentvars-display-logo.png') }}" alt="Pentecost University">
</header>
<p class="actions">
    <button onclick="window.print()">Print</button>
    <a href="{{ route('reports.students.export', ['format' => 'csv'] + request()->query()) }}">CSV</a>
    <a href="{{ route('reports.students.export', ['format' => 'xlsx'] + request()->query()) }}">Excel</a>
    <a href="{{ route('reports.students.export', ['format' => 'pdf'] + request()->query()) }}">PDF</a>
</p>
@include('reports.partials.students-table', ['rows' => $rows])
</body>
</html>
