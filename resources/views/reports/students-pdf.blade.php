<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 9px; color: #0f172a; }
        h1 { color: #082f63; font-size: 18px; margin-bottom: 2px; }
        table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        th, td { border: 1px solid #94a3b8; padding: 5px; text-align: left; vertical-align: top; line-height: 1.35; word-wrap: break-word; }
        th { background: #eff6ff; color: #082f63; }
        th:nth-child(1), td:nth-child(1) { width: 20%; }
        th:nth-child(2), td:nth-child(2) { width: 28%; }
        th:nth-child(3), td:nth-child(3) { width: 14%; }
        th:nth-child(4), td:nth-child(4) { width: 24%; }
        th:nth-child(5), td:nth-child(5) { width: 14%; }
    </style>
</head>
<body>
<h1>Pentecost University Scholarship Student Report</h1>
<p>Generated {{ $generatedAt->format('Y-m-d H:i') }} | {{ $rows->count() }} record(s)</p>
@include('reports.partials.students-table', ['rows' => $rows])
</body>
</html>
