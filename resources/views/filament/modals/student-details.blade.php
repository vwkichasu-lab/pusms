@php
    $photo = $student->profile_photo && \Illuminate\Support\Facades\Storage::disk('public')->exists($student->profile_photo)
        ? \Illuminate\Support\Facades\Storage::disk('public')->url($student->profile_photo)
        : null;
@endphp

<div style="display:grid; gap:16px;">
    <div style="display:flex; gap:16px; align-items:flex-start;">
        <div style="width:120px; height:140px; border:1px solid #cbd5e1; background:#fff; display:flex; align-items:center; justify-content:center; overflow:hidden;">
            @if ($photo)
                <img src="{{ $photo }}" alt="{{ $student->full_name }}" style="width:100%; height:100%; object-fit:cover;">
            @else
                <span style="color:#64748b; font-size:13px; text-align:center;">No passport photo</span>
            @endif
        </div>
        <div>
            <h3 style="margin:0; font-size:20px; font-weight:800;">{{ $student->full_name }}</h3>
            <p style="margin:6px 0 0;">{{ $student->student_id }}</p>
            <p style="margin:6px 0 0;">{{ $student->programme?->name ?: 'No programme' }}</p>
            <p style="margin:6px 0 0;">{{ $student->level?->name ?: 'No level' }}</p>
        </div>
    </div>

    <div style="overflow:auto;">
        <table style="width:100%; min-width:680px; border-collapse:collapse;">
            @foreach ([
                'Email' => $student->email ?: '-',
                'Phone' => $student->phone ?: '-',
                'Status' => str($student->student_status)->headline(),
                'Student Type' => $student->alumni_status === 'alumni' ? 'Alumni' : 'Continuing Student',
                'Alumni Badge' => $student->alumni_badge ?: '-',
                'Admission Year' => $student->admission_year ?: '-',
                'Graduation Year' => $student->graduation_year ?: '-',
                'Country' => $student->country ?: '-',
                'Region' => $student->region ?: '-',
                'District' => $student->district ?: '-',
            ] as $label => $value)
                <tr>
                    <th style="border:1px solid #cbd5e1; padding:8px; text-align:left; width:220px;">{{ $label }}</th>
                    <td style="border:1px solid #cbd5e1; padding:8px;">{{ $value }}</td>
                </tr>
            @endforeach
        </table>
    </div>
</div>
