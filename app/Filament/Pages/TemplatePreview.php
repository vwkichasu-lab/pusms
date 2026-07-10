<?php

namespace App\Filament\Pages;

use App\Services\TemplateVariableService;
use BackedEnum;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class TemplatePreview extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedEye;

    protected static string|UnitEnum|null $navigationGroup = 'Communication';

    protected static ?int $navigationSort = 3;

    protected string $view = 'filament.pages.template-preview';

    public array $data = [
        'message' => 'Dear {{student_name}}, your scholarship record for {{programme}} is ready.',
        'preview' => '',
    ];

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Section::make('Template Variable Preview')
                    ->description('Supported variables: {{student_name}}, {{student_id}}, {{scholarship_name}}, {{academic_year}}, {{level}}, {{programme}}.')
                    ->schema([
                        Textarea::make('message')->required()->rows(8),
                        Textarea::make('preview')->disabled()->rows(8),
                    ])
                    ->columns(2),
            ]);
    }

    public function preview(TemplateVariableService $templates): void
    {
        $state = $this->form->getState();
        $this->data['preview'] = $templates->render($state['message']);

        Notification::make()->title('Preview updated')->success()->send();
    }
}
