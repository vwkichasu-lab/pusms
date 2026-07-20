<?php

namespace App\Filament\Resources\Sponsors\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use App\Models\StudentScholarship;
use Illuminate\Database\Eloquent\Builder;

class SponsorsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('sponsor_type')->label('Type')->searchable()->sortable()->toggleable(),
                TextColumn::make('contact_person')->searchable()->toggleable(),
                TextColumn::make('email')->searchable()->toggleable(),
                TextColumn::make('phone')->searchable()->toggleable(),
                TextColumn::make('status')->badge()->sortable(),
                TextColumn::make('scholarship_programmes_count')->counts('scholarshipProgrammes')->label('Programmes')->sortable(),
                TextColumn::make('new_awards_count')
                    ->label('Newly Awarded')
                    ->getStateUsing(fn ($record): int => $record->scholarshipProgrammes()
                        ->withCount(['studentScholarships as stage_count' => fn (Builder $query): Builder => $query->where('scholarship_stage', StudentScholarship::STAGE_NEW_AWARD)])
                        ->get()
                        ->sum('stage_count'))
                    ->sortable(false),
                TextColumn::make('existing_beneficiaries_count')
                    ->label('Existing Beneficiaries')
                    ->getStateUsing(fn ($record): int => $record->scholarshipProgrammes()
                        ->withCount(['studentScholarships as stage_count' => fn (Builder $query): Builder => $query->where('scholarship_stage', StudentScholarship::STAGE_EXISTING_BENEFICIARY)])
                        ->get()
                        ->sum('stage_count'))
                    ->sortable(false),
            ])
            ->filters([
                SelectFilter::make('status')->options(['active' => 'Active', 'inactive' => 'Inactive']),
                SelectFilter::make('scholarship_stage')
                    ->label('Scholarship Stage')
                    ->options(StudentScholarship::stageOptions())
                    ->query(fn (Builder $query, array $data): Builder => filled($data['value'] ?? null)
                        ? $query->whereHas('scholarshipProgrammes.studentScholarships', fn (Builder $scholarship): Builder => $scholarship->where('scholarship_stage', $data['value']))
                        : $query),
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make()->requiresConfirmation(),
                DeleteAction::make()->requiresConfirmation(),
                RestoreAction::make(),
                ForceDeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->requiresConfirmation(),
                    RestoreBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                ]),
            ]);
    }
}
