<?php

namespace App\Filament\Resources\ResultImports;

use App\Filament\Resources\ResultImports\Pages\CreateResultImport;
use App\Filament\Resources\ResultImports\Pages\EditResultImport;
use App\Filament\Resources\ResultImports\Pages\ListResultImports;
use App\Filament\Resources\ResultImports\Schemas\ResultImportForm;
use App\Filament\Resources\ResultImports\Tables\ResultImportsTable;
use App\Models\ResultImport;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class ResultImportResource extends Resource
{
    protected static ?string $model = ResultImport::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string|UnitEnum|null $navigationGroup = 'Students';

    protected static ?int $navigationSort = 6;

    protected static ?string $recordTitleAttribute = 'original_filename';

    public static function form(Schema $schema): Schema
    {
        return ResultImportForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ResultImportsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListResultImports::route('/'),
            'edit' => EditResultImport::route('/{record}/edit'),
        ];
    }
}
