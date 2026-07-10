<?php

namespace App\Filament\Resources\Students\Schemas;

use App\Models\Department;
use App\Models\GhanaDistrict;
use App\Models\GhanaRegion;
use App\Models\Programme;
use App\Models\ScholarshipProgramme;
use App\Models\AcademicYear;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class StudentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Student Identity')
                    ->schema([
                        TextInput::make('student_id')
                            ->label('Student ID')
                            ->required()
                            ->maxLength(50)
                            ->unique(ignoreRecord: true),
                        TextInput::make('first_name')->required()->maxLength(255),
                        TextInput::make('middle_name')->maxLength(255),
                        TextInput::make('last_name')->required()->maxLength(255),
                        TextInput::make('email')->email()->required()->maxLength(255)->unique(ignoreRecord: true),
                        TextInput::make('phone')->tel()->required()->maxLength(30),
                        DatePicker::make('date_of_birth'),
                        FileUpload::make('profile_photo')
                            ->image()
                            ->disk('public')
                            ->directory('student-profile-photos')
                            ->visibility('public')
                            ->imageEditor()
                            ->maxSize(2048),
                    ])
                    ->columnSpanFull()
                    ->columns([
                        'default' => 1,
                        'md' => 2,
                        'xl' => 3,
                    ]),
                Section::make('Academic Details')
                    ->schema([
                        Select::make('programme_id')
                            ->relationship('programme', 'name')
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                Select::make('department_id')
                                    ->label('Department')
                                    ->relationship('department', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required(),
                                TextInput::make('name')->required()->maxLength(255),
                                TextInput::make('code')
                                    ->maxLength(50)
                                    ->helperText('Leave blank to auto-generate from the programme name.'),
                            ])
                            ->createOptionUsing(function (array $data): int {
                                $name = trim((string) $data['name']);

                                return Programme::create([
                                    'department_id' => $data['department_id'],
                                    'name' => $name,
                                    'code' => filled($data['code'] ?? null)
                                        ? Str::upper(trim((string) $data['code']))
                                        : Str::upper(Str::of($name)->replaceMatches('/[^A-Za-z0-9]+/', '')->substr(0, 12)),
                                    'status' => 'active',
                                ])->id;
                            })
                            ->required(),
                        Select::make('level_id')
                            ->relationship('level', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('admission_year')
                            ->required()
                            ->numeric()
                            ->minValue(1990)
                            ->maxValue((int) now()->format('Y') + 1),
                        TextInput::make('student_batch')->label('Batch / Cohort')->maxLength(255),
                        Select::make('student_status')
                            ->required()
                            ->options([
                                'active' => 'Active',
                                'graduated' => 'Graduated',
                                'deferred' => 'Deferred',
                                'withdrawn' => 'Withdrawn',
                                'suspended' => 'Suspended',
                            ])
                            ->default('active'),
                        TextInput::make('graduation_year')
                            ->numeric()
                            ->minValue(1990)
                            ->maxValue((int) now()->format('Y') + 8),
                        Select::make('alumni_status')
                            ->required()
                            ->options([
                                'not_alumni' => 'Continuing Student',
                                'alumni' => 'Alumni',
                            ])
                            ->default('not_alumni'),
                        TextInput::make('alumni_badge')
                            ->label('Alumni Badge')
                            ->maxLength(255),
                    ])
                    ->columnSpanFull()
                    ->columns([
                        'default' => 1,
                        'md' => 2,
                        'xl' => 3,
                    ]),
                Section::make('Location')
                    ->schema([
                        TextInput::make('home_town')->maxLength(255),
                        Select::make('country')
                            ->searchable()
                            ->options(self::countryOptions())
                            ->default('Ghana')
                            ->live()
                            ->required(),
                        Select::make('region')
                            ->searchable()
                            ->options(fn (): array => GhanaRegion::query()->orderBy('name')->pluck('name', 'name')->all())
                            ->visible(fn ($get): bool => ($get('country') ?? 'Ghana') === 'Ghana')
                            ->live()
                            ->afterStateUpdated(fn ($set): mixed => $set('district', null)),
                        TextInput::make('region')
                            ->label('Region / State')
                            ->maxLength(255)
                            ->visible(fn ($get): bool => ($get('country') ?? 'Ghana') !== 'Ghana'),
                        Select::make('district')
                            ->label('District / Municipal / Metropolitan')
                            ->searchable()
                            ->options(function ($get): array {
                                $region = $get('region');

                                if (! $region) {
                                    return [];
                                }

                                return GhanaDistrict::query()
                                    ->whereHas('region', fn ($query) => $query->where('name', $region))
                                    ->orderBy('name')
                                    ->pluck('name', 'name')
                                    ->all();
                            })
                            ->visible(fn ($get): bool => ($get('country') ?? 'Ghana') === 'Ghana'),
                        TextInput::make('district')
                            ->label('District / City')
                            ->maxLength(255)
                            ->visible(fn ($get): bool => ($get('country') ?? 'Ghana') !== 'Ghana'),
                    ])
                    ->columnSpanFull()
                    ->columns([
                        'default' => 1,
                        'md' => 2,
                        'xl' => 3,
                    ]),
                Section::make('Scholarship Assignment')
                    ->description('Optional: assign the student to a Type Of Scholarship while creating the student. Sponsors, Church Area, District / PIWC / Local are managed inside Type Of Scholarship.')
                    ->visibleOn('create')
                    ->schema([
                        Select::make('scholarship_award.scholarship_programme_id')
                            ->label('Type Of Scholarship')
                            ->options(fn (): array => ScholarshipProgramme::query()->orderBy('name')->pluck('name', 'id')->all())
                            ->searchable()
                            ->live()
                            ->afterStateUpdated(function (?int $state, Set $set): void {
                                $programme = $state ? ScholarshipProgramme::find($state) : null;

                                if (! $programme) {
                                    return;
                                }

                                if ($programme->academic_year_id) {
                                    $set('scholarship_award.academic_year_id', $programme->academic_year_id);
                                }

                                $set('scholarship_award.coverage_percentage', $programme->default_coverage_percentage);
                                $set('scholarship_award.covers_accommodation', $programme->default_covers_accommodation);
                                $set('scholarship_award.covers_tuition', true);
                            }),
                        Select::make('scholarship_award.academic_year_id')
                            ->label('Academic Year')
                            ->options(fn (): array => AcademicYear::query()->orderByDesc('start_date')->pluck('name', 'id')->all())
                            ->searchable(),
                        TextInput::make('scholarship_award.coverage_percentage')
                            ->label('Scholarship Percentage')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->suffix('%'),
                        Toggle::make('scholarship_award.covers_tuition')
                            ->label('Covers Tuition')
                            ->default(true),
                        Toggle::make('scholarship_award.covers_accommodation')
                            ->label('Includes Accommodation')
                            ->default(false),
                        DatePicker::make('scholarship_award.award_date')
                            ->label('Award Date'),
                        Textarea::make('scholarship_award.remarks')
                            ->label('Scholarship Notes')
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull()
                    ->columns([
                        'default' => 1,
                        'md' => 2,
                        'xl' => 3,
                    ]),
            ]);
    }

    /**
     * @return array<string, string>
     */
    private static function countryOptions(): array
    {
        return [
            'Ghana' => 'Ghana',
            'Nigeria' => 'Nigeria',
            'Togo' => 'Togo',
            'Benin' => 'Benin',
            'Cote d’Ivoire' => 'Cote d’Ivoire',
            'Burkina Faso' => 'Burkina Faso',
            'Liberia' => 'Liberia',
            'Sierra Leone' => 'Sierra Leone',
            'Other' => 'Other',
        ];
    }

}
