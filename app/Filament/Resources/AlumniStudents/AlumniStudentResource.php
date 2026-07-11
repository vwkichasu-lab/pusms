<?php

namespace App\Filament\Resources\AlumniStudents;

use App\Filament\Resources\AlumniStudents\Pages\CreateAlumniStudent;
use App\Filament\Resources\AlumniStudents\Pages\EditAlumniStudent;
use App\Filament\Resources\AlumniStudents\Pages\ListAlumniStudents;
use App\Filament\Resources\Students\Schemas\StudentForm;
use App\Filament\Resources\Students\Tables\StudentsTable;
use App\Models\Student;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class AlumniStudentResource extends Resource
{
    protected static ?string $model = Student::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static string|UnitEnum|null $navigationGroup = 'Students';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Alumni';

    protected static ?string $modelLabel = 'Alumni';

    protected static ?string $pluralModelLabel = 'Alumni';

    public static function form(Schema $schema): Schema
    {
        return StudentForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return StudentsTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('alumni_status', 'alumni');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAlumniStudents::route('/'),
            'create' => CreateAlumniStudent::route('/create'),
            'edit' => EditAlumniStudent::route('/{record}/edit'),
        ];
    }
}
