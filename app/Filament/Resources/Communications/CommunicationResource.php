<?php

namespace App\Filament\Resources\Communications;

use App\Filament\Resources\Communications\Pages\CreateCommunication;
use App\Filament\Resources\Communications\Pages\EditCommunication;
use App\Filament\Resources\Communications\Pages\ListCommunications;
use App\Filament\Resources\Communications\Schemas\CommunicationForm;
use App\Filament\Resources\Communications\Tables\CommunicationsTable;
use App\Models\Communication;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class CommunicationResource extends Resource
{
    protected static ?string $model = Communication::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPaperAirplane;

    protected static string|UnitEnum|null $navigationGroup = 'Communication';

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'Compose Message';

    protected static ?string $pluralModelLabel = 'Communication History';

    public static function form(Schema $schema): Schema
    {
        return CommunicationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CommunicationsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['subject', 'message', 'communication_type', 'status'];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCommunications::route('/'),
            'create' => CreateCommunication::route('/create'),
            'edit' => EditCommunication::route('/{record}/edit'),
        ];
    }
}
