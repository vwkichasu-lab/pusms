<!DOCTYPE html>
<html>
<head>
    <title>PUSMS Global Search</title>
    <style>
        body { background: #fff; color: #0f172a; font-family: Arial, sans-serif; margin: 24px; }
        form { border: 1px solid #cbd5e1; padding: 16px; margin-bottom: 18px; display:flex; gap:8px; flex-wrap:wrap; }
        input { border: 1px solid #94a3b8; padding: 10px; min-width: 320px; }
        button, a.btn { border: 1px solid #082f63; background: #082f63; color: #fff; padding: 10px 14px; text-decoration: none; }
        .table-wrap { overflow-x:auto; border:1px solid #cbd5e1; }
        table { width:100%; border-collapse:collapse; min-width:1200px; }
        th, td { border-bottom:1px solid #e2e8f0; padding:10px 12px; text-align:left; vertical-align:top; white-space:normal; }
        th { background:#f8fafc; font-size:13px; }
        tbody tr:hover { background:#f8fafc; }
        .empty { padding:24px; text-align:center; color:#475569; }
        .action { color:#082f63; font-weight:700; }
        @media (max-width: 760px) {
            body { margin: 12px; }
            input { min-width: 100%; }
            h1 { font-size: 24px; }
        }
    </style>
</head>
<body>
<h1>PUSMS Global Student Search</h1>
<form method="get">
    <input name="q" value="{{ $query }}" placeholder="Student ID, name, email, or phone">
    <button>Search</button>
    <a class="btn" href="/admin">Back to Dashboard</a>
</form>
<div class="table-wrap">
    <table>
        <thead>
        <tr>
            <th>Select</th>
            <th>Student ID</th>
            <th>Student Name</th>
            <th>Programme</th>
            <th>Faculty</th>
            <th>Level</th>
            <th>Scholarship Type</th>
            <th>Status</th>
            <th>Found In</th>
            <th>Action</th>
        </tr>
        </thead>
        <tbody>
        @forelse ($rows as $row)
            <tr>
                <td><input type="checkbox" value="{{ $row['id'] }}"></td>
                <td>{{ $row['student_id'] }}</td>
                <td><strong>{{ $row['name'] }}</strong><br>{{ $row['email'] }}</td>
                <td>{{ $row['programme'] }}</td>
                <td>{{ $row['school'] }}</td>
                <td>{{ $row['level'] }}</td>
                <td>{{ $row['scholarship'] ?: 'N/A' }}</td>
                <td>{{ str($row['status'])->headline() }}</td>
                <td>{{ $row['found_in'] }}</td>
                <td><a class="action" href="{{ $row['action_url'] }}">Open Record</a></td>
            </tr>
        @empty
            <tr><td class="empty" colspan="10">No matching records found.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
</body>
</html>
