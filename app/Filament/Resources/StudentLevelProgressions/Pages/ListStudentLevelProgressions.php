<?php

namespace App\Filament\Resources\StudentLevelProgressions\Pages;

use App\Filament\Resources\StudentLevelProgressions\StudentLevelProgressionResource;
use App\Models\AcademicYear;
use App\Models\Level;
use App\Models\Student;
use App\Models\StudentLevelProgression;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;

class ListStudentLevelProgressions extends ListRecords
{
    protected static string $resource = StudentLevelProgressionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('bulkMigrateStudents')
                ->label('Bulk Migrate Students')
                ->icon('heroicon-m-arrow-trending-up')
                ->form([
                    CheckboxList::make('student_ids')
                        ->label('Students')
                        ->options(fn (): array => Student::query()
                            ->with('level')
                            ->orderBy('student_id')
                            ->get()
                            ->mapWithKeys(fn (Student $student): array => [
                                $student->id => "{$student->student_id} - {$student->full_name} ({$student->level?->name})",
                            ])
                            ->all())
                        ->searchable()
                        ->bulkToggleable()
                        ->columns(1)
                        ->required(),
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
                ->action(function (array $data): void {
                    Student::query()
                        ->whereKey($data['student_ids'])
                        ->get()
                        ->each(function (Student $student) use ($data): void {
                            $previousLevelId = $student->level_id;

                            $student->update(['level_id' => $data['new_level_id']]);

                            StudentLevelProgression::create([
                                'student_id' => $student->id,
                                'previous_level_id' => $previousLevelId,
                                'new_level_id' => $data['new_level_id'],
                                'academic_year_id' => $data['academic_year_id'] ?? null,
                                'updated_by' => Auth::id(),
                                'update_type' => 'bulk',
                                'notes' => $data['notes'] ?? null,
                            ]);
                        });
                }),
            CreateAction::make(),
        ];
    }
}
