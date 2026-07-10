<?php

namespace App\Filament\Resources\Students\Pages;

use App\Filament\Resources\Students\StudentResource;
use App\Models\StudentLevelProgression;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditStudent extends EditRecord
{
    protected static string $resource = StudentResource::class;

    protected ?int $previousLevelId = null;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $this->previousLevelId = $this->record->level_id;

        return $data;
    }

    protected function afterSave(): void
    {
        if ($this->previousLevelId === $this->record->level_id) {
            return;
        }

        StudentLevelProgression::create([
            'student_id' => $this->record->id,
            'previous_level_id' => $this->previousLevelId,
            'new_level_id' => $this->record->level_id,
            'updated_by' => Auth::id(),
            'update_type' => 'individual',
        ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
