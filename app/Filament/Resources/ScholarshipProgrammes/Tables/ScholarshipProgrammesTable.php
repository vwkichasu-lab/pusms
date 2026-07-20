<?php

namespace App\Filament\Resources\ScholarshipProgrammes\Tables;

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

class ScholarshipProgrammesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('code')->searchable()->sortable(),
                TextColumn::make('sponsor.name')->label('Sponsor')->searchable()->sortable(),
                TextColumn::make('academicYear.name')->label('Academic Year')->sortable()->toggleable(),
                TextColumn::make('coverage_type')->badge()->sortable(),
                TextColumn::make('default_coverage_percentage')->label('Default %')->suffix('%')->sortable()->toggleable(),
                TextColumn::make('default_covers_accommodation')->label('Accommodation')->badge()->formatStateUsing(fn ($state): string => $state ? 'Included' : 'Excluded')->toggleable(),
                TextColumn::make('is_renewable')->label('Renewable')->badge()->formatStateUsing(fn ($state): string => $state ? 'Yes' : 'No')->toggleable(),
                TextColumn::make('scholarship_type')->label('Type')->badge()->sortable(),
                TextColumn::make('churchArea.name')->label('Church Area')->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('churchDistrict.name')->label('Church District')->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('status')->badge()->sortable(),
                TextColumn::make('student_scholarships_count')->counts('studentScholarships')->label('Students')->sortable(),
            ])
            ->filters([
                SelectFilter::make('sponsor')->relationship('sponsor', 'name')->searchable()->preload(),
                SelectFilter::make('academicYear')->label('Academic Year')->relationship('academicYear', 'name')->searchable()->preload(),
                SelectFilter::make('coverage_type')->options([
                    'full' => 'Full',
                    'partial' => 'Partial',
                    'tuition' => 'Tuition',
                    'stipend' => 'Stipend',
                ]),
                SelectFilter::make('scholarship_type')
                    ->label('Scholarship Type')
                    ->options([
                        'pu_bursary' => 'PU Bursary',
                        'area' => 'Area Scholarship',
                        'copcef' => 'COPCEF',
                        'sponsor' => 'Sponsor Scholarship',
                        'other' => 'Other',
                    ]),
                SelectFilter::make('status')->options(['active' => 'Active', 'inactive' => 'Inactive']),
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
