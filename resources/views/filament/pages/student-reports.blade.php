<x-filament-panels::page>
    <x-filament::section>
        <x-slot name="heading">Student Reports and Exports</x-slot>
        <x-slot name="description">Open a printable student scholarship report, then export it as CSV, Excel, or PDF.</x-slot>

        <div style="display:flex; gap:12px; flex-wrap:wrap;">
            <a class="fi-btn" href="{{ route('reports.students') }}" target="_blank">Open Printable Report</a>
            <a class="fi-btn" href="{{ route('reports.students.export', ['format' => 'csv']) }}">Download CSV</a>
            <a class="fi-btn" href="{{ route('reports.students.export', ['format' => 'xlsx']) }}">Download Excel</a>
            <a class="fi-btn" href="{{ route('reports.students.export', ['format' => 'pdf']) }}">Download PDF</a>
        </div>
    </x-filament::section>
</x-filament-panels::page>
