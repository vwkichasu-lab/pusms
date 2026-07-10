<?php

namespace App\Filament\Resources\ScholarshipProgrammes\Schemas;

use App\Models\ChurchDistrict;
use App\Models\ChurchArea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ScholarshipProgrammeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Type Of Scholarship Details')
                    ->schema([
                        Select::make('sponsor_id')
                            ->relationship('sponsor', 'name')
                            ->searchable()
                            ->preload(),
                        Select::make('academic_year_id')
                            ->label('Default Academic Year')
                            ->relationship('academicYear', 'name')
                            ->searchable()
                            ->preload(),
                        TextInput::make('name')->required()->maxLength(255),
                        TextInput::make('code')->required()->maxLength(50)->unique(ignoreRecord: true),
                        Select::make('coverage_type')
                            ->required()
                            ->options([
                                'full' => 'Full',
                                'partial' => 'Partial',
                                'tuition' => 'Tuition',
                                'stipend' => 'Stipend',
                            ])
                            ->default('partial'),
                        TextInput::make('default_coverage_percentage')
                            ->label('Default Scholarship Percentage')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->suffix('%'),
                        Toggle::make('default_covers_accommodation')
                            ->label('Default Includes Accommodation'),
                        Toggle::make('is_renewable')
                            ->label('Renewable Each Academic Year')
                            ->default(true),
                        Select::make('scholarship_type')
                            ->label('Scholarship Type')
                            ->required()
                            ->live()
                            ->options([
                                'pu_bursary' => 'PU Bursary',
                                'area' => 'Area Scholarship',
                                'copcef' => 'COPCEF',
                                'sponsor' => 'Sponsor Scholarship',
                                'other' => 'Other',
                            ])
                            ->default('pu_bursary')
                            ->afterStateUpdated(function ($state, $set): void {
                                $requiresArea = $state === 'area';
                                $set('requires_church_area', $requiresArea);
                                $set('requires_church_district', false);

                                if (! $requiresArea) {
                                    $set('church_area_id', null);
                                    $set('church_district_id', null);
                                }
                            }),
                        Toggle::make('requires_church_area')
                            ->label('Requires Church Area')
                            ->live()
                            ->visible(fn ($get): bool => in_array($get('scholarship_type'), ['area', 'copcef', 'other'], true)),
                        Toggle::make('requires_church_district')
                            ->label('Requires Church District')
                            ->live()
                            ->visible(fn ($get): bool => (bool) $get('requires_church_area')),
                        Select::make('church_area_id')
                            ->label('Church of Pentecost Area')
                            ->relationship('churchArea', 'name')
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                TextInput::make('name')->label('Area Name')->required()->maxLength(255),
                            ])
                            ->createOptionUsing(fn (array $data): int => ChurchArea::create([
                                'name' => $data['name'],
                                'status' => 'active',
                            ])->id)
                            ->live()
                            ->visible(fn ($get): bool => (bool) $get('requires_church_area'))
                            ->afterStateUpdated(fn ($set): mixed => $set('church_district_id', null)),
                        Select::make('church_district_id')
                            ->label('Church District / PIWC / Local')
                            ->searchable()
                            ->options(fn ($get): array => ChurchDistrict::query()
                                ->when($get('church_area_id'), fn ($query, $areaId) => $query->where('church_area_id', $areaId))
                                ->orderBy('name')
                                ->pluck('name', 'id')
                                ->all())
                            ->createOptionForm([
                                Select::make('church_area_id')
                                    ->label('Area')
                                    ->options(fn (): array => ChurchArea::query()->orderBy('name')->pluck('name', 'id')->all())
                                    ->searchable()
                                    ->required(),
                                TextInput::make('name')->label('District / PIWC / Local Name')->required()->maxLength(255),
                            ])
                            ->createOptionUsing(fn (array $data): int => ChurchDistrict::create([
                                'church_area_id' => $data['church_area_id'],
                                'name' => $data['name'],
                                'status' => 'active',
                            ])->id)
                            ->visible(fn ($get): bool => (bool) $get('requires_church_area') && (bool) $get('requires_church_district')),
                        Select::make('status')
                            ->required()
                            ->options(['active' => 'Active', 'inactive' => 'Inactive'])
                            ->default('active'),
                        Textarea::make('description')->columnSpanFull(),
                        Textarea::make('eligibility_criteria')
                            ->placeholder('Example: Continuing students with financial need, good academic performance, and good conduct.')
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
}
