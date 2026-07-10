<div class="space-y-4" style="max-height:520px; overflow:auto; padding-right:8px;">
    <div>
        <strong>Code:</strong> {{ $faculty->code }}<br>
        <strong>Status:</strong> {{ str($faculty->status)->headline() }}
    </div>

    @foreach ($faculty->departments as $department)
        <div style="border:1px solid #cbd5e1; padding:10px;">
            <strong>{{ $department->name }}</strong>
            <ul style="list-style:disc; margin-left:20px; margin-top:6px;">
                @forelse ($department->programmes as $programme)
                    <li>{{ $programme->code }} - {{ $programme->name }}</li>
                @empty
                    <li>No programmes linked yet.</li>
                @endforelse
            </ul>
        </div>
    @endforeach
</div>
