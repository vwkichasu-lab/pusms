<table>
    <thead>
    <tr>
        <th>Student</th>
        <th>Academic</th>
        <th>Location</th>
        <th>Scholarship</th>
        <th>Performance</th>
    </tr>
    </thead>
    <tbody>
    @forelse ($rows as $row)
        <tr>
            <td>
                <strong>{{ $row['student_id'] }}</strong><br>
                {{ $row['name'] }}<br>
                {{ $row['email'] }}<br>
                {{ $row['phone'] }}
            </td>
            <td>
                {{ $row['programme'] }}<br>
                <small>{{ $row['department'] }} / {{ $row['school'] }}</small><br>
                {{ $row['level'] }}<br>
                Status: {{ str($row['status'])->headline() }}<br>
                Type: {{ $row['alumni_status'] === 'alumni' ? 'Alumni' : 'Continuing Student' }}
                @if ($row['completion_year'])
                    <br>Completion: {{ $row['completion_year'] }}
                @endif
            </td>
            <td>
                {{ $row['region'] ?: 'N/A' }}<br>
                {{ $row['district'] ?: 'N/A' }}
            </td>
            <td>
                {{ $row['scholarship'] ?: 'No scholarship recorded' }}<br>
                @if ($row['sponsor'])
                    Sponsor: {{ $row['sponsor'] }}<br>
                @endif
                @if ($row['coverage_percentage'])
                    Coverage: {{ $row['coverage_percentage'] }}%<br>
                @endif
                Accommodation: {{ $row['accommodation'] ?: 'N/A' }}<br>
                Year: {{ $row['scholarship_year'] ?: 'N/A' }}
            </td>
            <td>
                GPA: {{ $row['gpa'] ?: 'N/A' }}<br>
                CGPA: {{ $row['cgpa'] ?: 'N/A' }}<br>
                {{ $row['performance_status'] ? str($row['performance_status'])->headline() : 'N/A' }}
            </td>
        </tr>
    @empty
        <tr><td colspan="5">No records found.</td></tr>
    @endforelse
    </tbody>
</table>
