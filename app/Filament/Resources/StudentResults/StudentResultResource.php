<?php

namespace App\Filament\Resources\StudentResults;

use App\Filament\Resources\StudentResults\Pages\CreateStudentResult;
use App\Filament\Resources\StudentResults\Pages\EditStudentResult;
use App\Filament\Resources\StudentResults\Pages\ListStudentResults;
use App\Filament\Resources\StudentResults\Schemas\StudentResultForm;
use App\Filament\Resources\StudentResults\Tables\StudentResultsTable;
use App\Models\StudentResult;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class StudentResultResource extends Resource
{
    protected static ?string $model = StudentResult::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBar;

    protected static string|UnitEnum|null $navigationGroup = 'Students';

    protected static ?int $navigationSort = 3;

    protected static ?string $modelLabel = 'Student Result';

    protected static ?string $pluralModelLabel = 'Student Results';

    public static function form(Schema $schema): Schema
    {
        return StudentResultForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return StudentResultsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['student.student_id', 'student.first_name', 'student.last_name', 'academicYear.name', 'performance_status'];
    }

    public static function getGlobalSearchResultTitle(Model $record): string
    {
        return ($record->student?->full_name ?? 'Student Result').' - GPA '.($record->gpa ?? 'N/A');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListStudentResults::route('/'),
            'create' => CreateStudentResult::route('/create'),
            'edit' => EditStudentResult::route('/{record}/edit'),
        ];
    }
}
