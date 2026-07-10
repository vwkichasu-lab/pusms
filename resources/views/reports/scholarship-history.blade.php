<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $student->full_name }} Scholarship History</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; background: #ffffff; color: #111827; font-family: Arial, sans-serif; font-size: 13px; }
        .page { max-width: 1120px; margin: 0 auto; padding: 24px; }
        .header { display: flex; align-items: center; justify-content: space-between; border-bottom: 2px solid #111827; padding-bottom: 14px; margin-bottom: 18px; }
        .brand { display: flex; align-items: center; gap: 12px; }
        .brand img { width: 54px; height: 54px; object-fit: contain; }
        h1 { margin: 0; font-size: 22px; }
        h2 { margin: 24px 0 10px; font-size: 16px; }
        .muted { color: #4b5563; }
        .grid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 10px; }
        .field { border: 1px solid #9ca3af; padding: 9px; min-height: 54px; }
        .label { display: block; color: #374151; font-size: 11px; text-transform: uppercase; margin-bottom: 4px; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th, td { border: 1px solid #9ca3af; padding: 8px; text-align: left; vertical-align: top; }
        th { background: #f9fafb; font-size: 12px; }
        .toolbar { margin-bottom: 14px; text-align: right; }
        .toolbar button { background: #ffffff; border: 1px solid #111827; padding: 8px 12px; cursor: pointer; }
        .badge { display: inline-block; border: 1px solid #6b7280; padding: 2px 6px; font-size: 12px; }
        @media print {
            .toolbar { display: none; }
            .page { max-width: none; padding: 10mm; }
        }
    </style>
</head>
<body>
    <main class="page">
        <div class="toolbar">
            <button type="button" onclick="window.print()">Print</button>
        </div>

        <header class="header">
            <div class="brand">
                <img src="{{ asset('images/pentvars-display-logo.png') }}" alt="Pentecost University">
                <div>
                    <h1>Scholarship History</h1>
                    <div class="muted">Pentecost University Scholarship Management System</div>
                </div>
            </div>
            <div class="muted">{{ now()->format('d M Y') }}</div>
        </header>

        <section class="grid">
            <div class="field"><span class="label">Student</span>{{ $student->full_name }}</div>
            <div class="field"><span class="label">Student ID</span>{{ $student->student_id }}</div>
            <div class="field"><span class="label">Programme</span>{{ $student->programme?->name ?? 'N/A' }}</div>
            <div class="field"><span class="label">School</span>{{ $student->programme?->department?->school?->name ?? 'N/A' }}</div>
            <div class="field"><span class="label">Level</span>{{ $student->level?->name ?? 'N/A' }}</div>
            <div class="field"><span class="label">Status</span>{{ str($student->student_status)->headline() }}</div>
            <div class="field"><span class="label">Alumni Badge</span>{{ $student->alumni_badge ?: 'N/A' }}</div>
            <div class="field"><span class="label">Region</span>{{ $student->region ?: 'N/A' }}</div>
        </section>

        <h2>Scholarship Awards</h2>
        <table>
            <thead>
                <tr>
                    <th>Programme</th>
                    <th>Sponsor</th>
                    <th>Academic Period</th>
                    <th>Coverage</th>
                    <th>Dates</th>
                    <th>Status</th>
                    <th>Reference</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($scholarships as $award)
                    <tr>
                        <td>{{ $award->scholarshipProgramme?->name ?? 'N/A' }}</td>
                        <td>{{ $award->scholarshipProgramme?->sponsor?->name ?? 'N/A' }}</td>
                        <td>{{ $award->academicYear?->name ?? 'N/A' }} / {{ $award->semester?->name ?? 'N/A' }}</td>
                        <td>
                            <strong>{{ number_format((float) $award->coverage_percentage, 0) }}%</strong><br>
                            <span class="badge">{{ $award->covers_tuition ? 'Tuition' : 'No tuition' }}</span>
                            <span class="badge">{{ $award->covers_accommodation ? 'Accommodation' : 'No accommodation' }}</span>
                            <span class="badge">{{ $award->covers_stipend ? 'Stipend' : 'No stipend' }}</span>
                            @if ($award->coverage_notes)
                                <br>{{ $award->coverage_notes }}
                            @endif
                        </td>
                        <td>
                            Award: {{ $award->award_date?->format('d M Y') ?? 'N/A' }}<br>
                            {{ $award->start_date?->format('d M Y') ?? 'N/A' }} - {{ $award->end_date?->format('d M Y') ?? 'N/A' }}
                        </td>
                        <td>{{ str($award->status)->headline() }}</td>
                        <td>{{ $award->award_reference ?: 'N/A' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7">No scholarship history recorded.</td></tr>
                @endforelse
            </tbody>
        </table>

        <h2>Academic Performance</h2>
        <table>
            <thead>
                <tr>
                    <th>Academic Year</th>
                    <th>Semester</th>
                    <th>GPA</th>
                    <th>CGPA</th>
                    <th>Performance</th>
                    <th>Remarks</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($results as $result)
                    <tr>
                        <td>{{ $result->academicYear?->name ?? 'N/A' }}</td>
                        <td>{{ $result->semester?->name ?? 'N/A' }}</td>
                        <td>{{ $result->gpa ?? 'N/A' }}</td>
                        <td>{{ $result->cgpa ?? 'N/A' }}</td>
                        <td>{{ str($result->performance_status)->headline() }}</td>
                        <td>{{ $result->remarks ?: 'N/A' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6">No academic performance recorded.</td></tr>
                @endforelse
            </tbody>
        </table>
    </main>
</body>
</html>
