<x-filament-panels::page>
    <x-filament::section>
        <x-slot name="heading">Global Student Search</x-slot>
        <x-slot name="description">Search students by ID, name, email, or phone with academic and scholarship summary.</x-slot>

        <form action="{{ route('global.student-search') }}" method="get" target="_blank" style="display:flex; gap:12px; flex-wrap:wrap;">
            <input name="q" placeholder="Search student records" style="border:1px solid #cbd5e1; padding:10px; min-width:320px;">
            <button class="fi-btn" type="submit">Search</button>
        </form>
    </x-filament::section>
</x-filament-panels::page>
