<?php

namespace App\Filament\Resources\Students\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use App\Models\AcademicYear;
use App\Models\Level;
use App\Models\StudentScholarship;
use App\Models\StudentLevelProgression;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class StudentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('profile_photo')
                    ->label('Photo')
                    ->disk('public')
                    ->circular()
                    ->toggleable(),
                TextColumn::make('student_id')->label('Student ID')->searchable()->sortable(),
                TextColumn::make('full_name')->label('Name')->searchable(['first_name', 'middle_name', 'last_name'])->sortable(['last_name', 'first_name']),
                TextColumn::make('email')->searchable()->toggleable(),
                TextColumn::make('phone')->searchable()->toggleable(),
                TextColumn::make('programme.name')->label('Programme')->searchable()->sortable(),
                TextColumn::make('programme.department.name')->label('Department')->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('programme.department.school.name')->label('School')->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('level.name')->label('Level')->sortable(),
                TextColumn::make('student_status')->badge()->sortable(),
                TextColumn::make('scholarship_stage')
                    ->label('Scholarship Stage')
                    ->badge()
                    ->getStateUsing(function ($record): ?string {
                        $stage = $record->scholarships()->latest()->value('scholarship_stage');

                        return $stage ? (StudentScholarship::stageOptions()[$stage] ?? $stage) : null;
                    })
                    ->toggleable(),
                TextColumn::make('student_batch')->label('Batch')->searchable()->toggleable(),
                TextColumn::make('alumni_status')->badge()->sortable()->toggleable(),
                TextColumn::make('alumni_badge')->searchable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('region')->searchable()->toggleable(),
            ])
            ->filters([
                SelectFilter::make('programme')->relationship('programme', 'name')->searchable()->preload(),
                SelectFilter::make('level')->relationship('level', 'name')->searchable()->preload(),
                SelectFilter::make('student_status')
                    ->options([
                        'active' => 'Active',
                        'graduated' => 'Graduated',
                        'deferred' => 'Deferred',
                        'withdrawn' => 'Withdrawn',
                        'suspended' => 'Suspended',
                    ]),
                SelectFilter::make('alumni_status')
                    ->options([
                        'not_alumni' => 'Not Alumni',
                        'alumni' => 'Alumni',
                    ]),
                SelectFilter::make('scholarship_stage')
                    ->label('Scholarship Stage')
                    ->options(StudentScholarship::stageOptions())
                    ->query(fn (Builder $query, array $data): Builder => filled($data['value'] ?? null)
                        ? $query->whereHas('scholarships', fn (Builder $scholarship): Builder => $scholarship->where('scholarship_stage', $data['value']))
                        : $query),
            ])
            ->recordActions([
                ViewAction::make()
                    ->label('Details')
                    ->modalHeading(fn ($record): string => $record->full_name)
                    ->modalContent(fn ($record) => view('filament.modals.student-details', ['student' => $record]))
                    ->slideOver(),
                Action::make('history')
                    ->label('History')
                    ->icon(Heroicon::OutlinedClipboardDocumentList)
                    ->url(fn ($record): string => route('students.scholarship-history', $record))
                    ->openUrlInNewTab(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('bulkLevelUpdate')
                        ->label('Bulk Level Update')
                        ->icon('heroicon-m-arrow-trending-up')
                        ->form([
                            Select::make('new_level_id')
                                ->label('New Level')
                                ->options(fn (): array => Level::query()->orderBy('numeric_value')->pluck('name', 'id')->all())
                                ->required(),
                            Select::make('academic_year_id')
                                ->label('Academic Year')
                                ->options(fn (): array => AcademicYear::query()->orderByDesc('start_date')->pluck('name', 'id')->all()),
                            Textarea::make('notes')->rows(3),
                        ])
                        ->requiresConfirmation()
                        ->action(function ($records, array $data): void {
                            foreach ($records as $student) {
                                $previousLevel = $student->level_id;
                                $student->update([
                                    'level_id' => $data['new_level_id'],
                                    'student_status' => Level::find($data['new_level_id'])?->numeric_value === 400 ? $student->student_status : $student->student_status,
                                ]);

                                StudentLevelProgression::create([
                                    'student_id' => $student->id,
                                    'previous_level_id' => $previousLevel,
                                    'new_level_id' => $data['new_level_id'],
                                    'academic_year_id' => $data['academic_year_id'] ?? null,
                                    'updated_by' => Auth::id(),
                                    'update_type' => 'bulk',
                                    'notes' => $data['notes'] ?? null,
                                ]);
                            }
                        }),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
