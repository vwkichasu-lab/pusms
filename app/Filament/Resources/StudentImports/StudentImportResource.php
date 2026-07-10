<?php

namespace App\Filament\Resources\StudentImports;

use App\Filament\Resources\StudentImports\Pages\CreateStudentImport;
use App\Filament\Resources\StudentImports\Pages\EditStudentImport;
use App\Filament\Resources\StudentImports\Pages\ListStudentImports;
use App\Filament\Resources\StudentImports\Schemas\StudentImportForm;
use App\Filament\Resources\StudentImports\Tables\StudentImportsTable;
use App\Models\StudentImport;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class StudentImportResource extends Resource
{
    protected static ?string $model = StudentImport::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentArrowUp;

    protected static string|UnitEnum|null $navigationGroup = 'Students';

    protected static ?int $navigationSort = 4;

    protected static ?string $modelLabel = 'Student Import Log';

    protected static ?string $pluralModelLabel = 'Student Import Logs';

    public static function form(Schema $schema): Schema
    {
        return StudentImportForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return StudentImportsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListStudentImports::route('/'),
            'create' => CreateStudentImport::route('/create'),
            'edit' => EditStudentImport::route('/{record}/edit'),
        ];
    }
}
