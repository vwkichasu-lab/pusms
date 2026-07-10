<?php

namespace App\Filament\Resources\StudentScholarships;

use App\Filament\Resources\StudentScholarships\Pages\CreateStudentScholarship;
use App\Filament\Resources\StudentScholarships\Pages\EditStudentScholarship;
use App\Filament\Resources\StudentScholarships\Pages\ListStudentScholarships;
use App\Filament\Resources\StudentScholarships\Schemas\StudentScholarshipForm;
use App\Filament\Resources\StudentScholarships\Tables\StudentScholarshipsTable;
use App\Models\StudentScholarship;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class StudentScholarshipResource extends Resource
{
    protected static ?string $model = StudentScholarship::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;

    protected static string|UnitEnum|null $navigationGroup = 'Scholarships';

    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = 'Scholarship Student';

    protected static ?string $pluralModelLabel = 'Scholarship Students';

    public static function form(Schema $schema): Schema
    {
        return StudentScholarshipForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return StudentScholarshipsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['student.student_id', 'student.first_name', 'student.last_name', 'scholarshipProgramme.name', 'award_reference'];
    }

    public static function getGlobalSearchResultTitle(Model $record): string
    {
        return ($record->student?->full_name ?? 'Scholarship Student').' - '.($record->scholarshipProgramme?->name ?? 'Scholarship');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListStudentScholarships::route('/'),
            'create' => CreateStudentScholarship::route('/create'),
            'edit' => EditStudentScholarship::route('/{record}/edit'),
        ];
    }
}
