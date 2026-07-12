<x-filament-widgets::widget>
    <x-filament::section>
        <style>
            .pusms-work-grid {
                display: grid;
                gap: 1rem;
                grid-template-columns: repeat(4, minmax(0, 1fr));
            }

            .pusms-dashboard-hero {
                display: grid;
                grid-template-columns: minmax(0, 1.2fr) minmax(220px, .8fr);
                gap: 1.25rem;
                align-items: center;
                margin-bottom: 1.25rem;
                border: 1px solid #cbd5e1;
                background: #ffffff;
                padding: 1.25rem;
            }

            .pusms-dashboard-hero h2 {
                margin: 0;
                color: #0f172a;
                font-size: 1.5rem;
                font-weight: 900;
            }

            .pusms-dashboard-hero p {
                margin: .5rem 0 0;
                color: #475569;
                font-size: .95rem;
                line-height: 1.6;
            }

            .pusms-dashboard-hero img {
                width: min(100%, 360px);
                max-height: 190px;
                object-fit: contain;
                justify-self: center;
            }

            .pusms-work-card,
            .pusms-phase-card {
                background: #ffffff;
                border: 1px solid #cbd5e1;
                padding: 1rem;
            }

            .pusms-work-card h3,
            .pusms-phase-card strong {
                margin: 0;
                color: #0f172a;
                font-size: .925rem;
                font-weight: 800;
            }

            .pusms-work-card p,
            .pusms-phase-card p {
                margin: .5rem 0 0;
                color: #475569;
                font-size: .875rem;
                line-height: 1.6;
            }

            .pusms-phase-card {
                margin-top: 1.25rem;
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 1rem;
            }

            .pusms-next-badge {
                display: inline-flex;
                width: fit-content;
                border: 1px solid #d69e2e;
                background: #fffaf0;
                color: #92400e;
                padding: .5rem .75rem;
                font-size: .875rem;
                font-weight: 800;
                white-space: nowrap;
            }

            .pusms-steps {
                margin-top: 1.25rem;
                background: #ffffff;
                border: 1px solid #cbd5e1;
                padding: 1rem;
            }

            .pusms-steps ol {
                margin: .75rem 0 0;
                padding-left: 1.25rem;
                color: #334155;
                font-size: .875rem;
                line-height: 1.7;
            }

            @media (max-width: 1100px) {
                .pusms-work-grid {
                    grid-template-columns: repeat(2, minmax(0, 1fr));
                }
            }

            @media (max-width: 720px) {
                .pusms-dashboard-hero {
                    grid-template-columns: 1fr;
                }

                .pusms-work-grid,
                .pusms-phase-card {
                    grid-template-columns: 1fr;
                }

                .pusms-phase-card {
                    align-items: flex-start;
                    flex-direction: column;
                }
            }
        </style>

        <x-slot name="heading">
            PUSMS Work Areas
        </x-slot>

        <x-slot name="description">
            Core work areas for managing scholarship students, awards, results, communication, reports, and imports.
        </x-slot>

        @php
            $hour = now()->hour;
            $greeting = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');
            $name = auth()->user()?->name ?: 'Administrator';
        @endphp

        <div class="pusms-dashboard-hero">
            <div>
                <h2>{{ $greeting }}, {{ $name }}</h2>
                <p>Welcome to the Pentecost University Scholarship Management System. Use the dashboard to monitor students, awards, communication, reports, and academic records.</p>
            </div>
            <img src="/images/pentvars-3d.png" alt="Pentecost University">
        </div>

        <div class="pusms-work-grid">
            @foreach ([
                ['label' => 'Academic Management', 'items' => 'Schools, departments, programmes, levels, academic years, semesters'],
                ['label' => 'Student Management', 'items' => 'Profiles, filters, imports, documents, scholarship history'],
                ['label' => 'Scholarships and Alumni', 'items' => 'Sponsors, programmes, assignments, coverage, history, alumni badges'],
                ['label' => 'Communication and Reports', 'items' => 'Gmail sending, exports, search, dashboard analytics'],
            ] as $area)
                <div class="pusms-work-card">
                    <h3>{{ $area['label'] }}</h3>
                    <p>{{ $area['items'] }}</p>
                </div>
            @endforeach
        </div>

        <div class="pusms-phase-card">
            <div>
                <strong>Current setup</strong>
                <p>Start with academic setup, create or import students, assign Types Of Scholarship, record GPA/results, then use reports and messaging for follow-up.</p>
            </div>
            <span class="pusms-next-badge">
                Ready for Daily Use
            </span>
        </div>

        <div class="pusms-steps">
            <strong>How to add a student</strong>
            <ol>
                <li>Create a School under Academic Management.</li>
                <li>Create a Department and connect it to the School.</li>
                <li>Create a Programme and connect it to the Department.</li>
                <li>Open Students, then create the student record.</li>
                <li>Create a Type Of Scholarship, then assign it under Scholarship Students.</li>
            </ol>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
