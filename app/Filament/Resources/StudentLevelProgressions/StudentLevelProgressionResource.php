<?php

namespace App\Filament\Resources\StudentLevelProgressions;

use App\Filament\Resources\StudentLevelProgressions\Pages\CreateStudentLevelProgression;
use App\Filament\Resources\StudentLevelProgressions\Pages\EditStudentLevelProgression;
use App\Filament\Resources\StudentLevelProgressions\Pages\ListStudentLevelProgressions;
use App\Filament\Resources\StudentLevelProgressions\Schemas\StudentLevelProgressionForm;
use App\Filament\Resources\StudentLevelProgressions\Tables\StudentLevelProgressionsTable;
use App\Models\StudentLevelProgression;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class StudentLevelProgressionResource extends Resource
{
    protected static ?string $model = StudentLevelProgression::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string|UnitEnum|null $navigationGroup = 'Students';

    protected static ?int $navigationSort = 7;

    protected static ?string $recordTitleAttribute = 'id';

    protected static ?string $modelLabel = 'Level Migration';

    protected static ?string $pluralModelLabel = 'Level Migration History';

    protected static ?string $navigationLabel = 'Level Migration History';

    public static function form(Schema $schema): Schema
    {
        return StudentLevelProgressionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return StudentLevelProgressionsTable::configure($table);
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
            'index' => ListStudentLevelProgressions::route('/'),
            'create' => CreateStudentLevelProgression::route('/create'),
            'edit' => EditStudentLevelProgression::route('/{record}/edit'),
        ];
    }
}
