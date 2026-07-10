<?php

namespace App\Filament\Resources\ScholarshipProgrammes;

use App\Filament\Resources\ScholarshipProgrammes\Pages\CreateScholarshipProgramme;
use App\Filament\Resources\ScholarshipProgrammes\Pages\EditScholarshipProgramme;
use App\Filament\Resources\ScholarshipProgrammes\Pages\ListScholarshipProgrammes;
use App\Filament\Resources\ScholarshipProgrammes\Schemas\ScholarshipProgrammeForm;
use App\Filament\Resources\ScholarshipProgrammes\Tables\ScholarshipProgrammesTable;
use App\Models\ScholarshipProgramme;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class ScholarshipProgrammeResource extends Resource
{
    protected static ?string $model = ScholarshipProgramme::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string|UnitEnum|null $navigationGroup = 'Scholarships';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $modelLabel = 'Type Of Scholarship';

    protected static ?string $pluralModelLabel = 'Types Of Scholarship';

    protected static ?string $navigationLabel = 'Types Of Scholarship';

    public static function form(Schema $schema): Schema
    {
        return ScholarshipProgrammeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ScholarshipProgrammesTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withoutGlobalScopes([
            SoftDeletingScope::class,
        ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'code', 'description', 'sponsor.name', 'churchArea.name', 'churchDistrict.name'];
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Code' => $record->code,
            'Type' => str($record->scholarship_type)->headline()->toString(),
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListScholarshipProgrammes::route('/'),
            'create' => CreateScholarshipProgramme::route('/create'),
            'edit' => EditScholarshipProgramme::route('/{record}/edit'),
        ];
    }
}
