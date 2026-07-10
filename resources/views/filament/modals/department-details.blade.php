<div class="space-y-4">
    <div>
        <strong>Faculty:</strong> {{ $department->school?->name }}<br>
        <strong>Code:</strong> {{ $department->code }}<br>
        <strong>Status:</strong> {{ str($department->status)->headline() }}
    </div>

    <div style="border:1px solid #cbd5e1; padding:10px; max-height:360px; overflow:auto;">
        <strong>Programmes</strong>
        <ul style="list-style:disc; margin-left:20px; margin-top:6px;">
            @forelse ($department->programmes as $programme)
                <li>{{ $programme->code }} - {{ $programme->name }}</li>
            @empty
                <li>No programmes linked yet.</li>
            @endforelse
        </ul>
    </div>
</div>
