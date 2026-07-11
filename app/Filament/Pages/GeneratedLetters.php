<?php

namespace App\Filament\Pages;

use App\Models\GeneratedScholarshipLetter;
use BackedEnum;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class GeneratedLetters extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentDuplicate;

    protected static string|UnitEnum|null $navigationGroup = 'Reports';

    protected static ?int $navigationSort = 3;

    protected static ?string $title = 'Generated Letters';

    protected string $view = 'filament.pages.generated-letters';

    public array $data = [];

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Section::make('Filter Letters')
                    ->schema([
                        DatePicker::make('from')->live(),
                        DatePicker::make('to')->live(),
                        Select::make('year')
                            ->options(fn (): array => GeneratedScholarshipLetter::query()
                                ->selectRaw("strftime('%Y', generated_at) as year")
                                ->distinct()
                                ->orderByDesc('year')
                                ->pluck('year', 'year')
                                ->all())
                            ->searchable()
                            ->live(),
                    ])
                    ->columns(3),
            ]);
    }

    /**
     * @return array<int, GeneratedScholarshipLetter>
     */
    public function getLettersProperty(): array
    {
        return GeneratedScholarshipLetter::query()
            ->with(['student.programme', 'award.scholarshipProgramme', 'generator'])
            ->when($this->data['from'] ?? null, fn (Builder $query, string $date): Builder => $query->whereDate('generated_at', '>=', $date))
            ->when($this->data['to'] ?? null, fn (Builder $query, string $date): Builder => $query->whereDate('generated_at', '<=', $date))
            ->when($this->data['year'] ?? null, fn (Builder $query, string $year): Builder => $query->whereYear('generated_at', $year))
            ->latest('generated_at')
            ->limit(200)
            ->get()
            ->all();
    }
}
