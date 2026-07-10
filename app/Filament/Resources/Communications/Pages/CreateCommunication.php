<?php

namespace App\Filament\Resources\Communications\Pages;

use App\Filament\Resources\Communications\CommunicationResource;
use App\Models\Sponsor;
use App\Models\Student;
use App\Services\CommunicationService;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class CreateCommunication extends CreateRecord
{
    protected static string $resource = CommunicationResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = Auth::id();
        $data['status'] = 'draft';

        return $data;
    }

    protected function afterCreate(): void
    {
        $recipientGroup = data_get($this->record->metadata, 'recipient_group', 'students');

        $students = collect();
        $sponsors = collect();

        if (in_array($recipientGroup, ['students', 'students_and_sponsors'], true)) {
            $students = Student::query()
            ->with(['level', 'programme'])
            ->when(data_get($this->record->metadata, 'programme_id'), fn (Builder $query, int|string $id): Builder => $query->where('programme_id', $id))
            ->when(data_get($this->record->metadata, 'level_id'), fn (Builder $query, int|string $id): Builder => $query->where('level_id', $id))
            ->when(data_get($this->record->metadata, 'school_id'), function (Builder $query, int|string $id): Builder {
                return $query->whereHas('programme.department', fn (Builder $department): Builder => $department->where('school_id', $id));
            })
            ->when(data_get($this->record->metadata, 'scholarship_type'), function (Builder $query, string $type): Builder {
                return $query->whereHas('scholarships.scholarshipProgramme', fn (Builder $scholarship): Builder => $scholarship->where('scholarship_type', $type));
            })
            ->when(data_get($this->record->metadata, 'scholarship_programme_id'), function (Builder $query, int|string $id): Builder {
                return $query->whereHas('scholarships', fn (Builder $scholarship): Builder => $scholarship->where('scholarship_programme_id', $id));
            })
            ->when(data_get($this->record->metadata, 'alumni_status'), fn (Builder $query, string $status): Builder => $query->where('alumni_status', $status))
            ->when(! (bool) data_get($this->record->metadata, 'send_all_students', true), function (Builder $query): Builder {
                $ids = collect(data_get($this->record->metadata, 'selected_student_ids', []))->filter()->all();

                return $query->whereKey($ids ?: [0]);
            })
            ->get();
        }

        if (in_array($recipientGroup, ['sponsors', 'students_and_sponsors'], true)) {
            $sponsors = Sponsor::query()
                ->when(data_get($this->record->metadata, 'sponsor_id'), fn (Builder $query, int|string $id): Builder => $query->whereKey($id))
                ->when(! (bool) data_get($this->record->metadata, 'send_all_sponsors', true), function (Builder $query): Builder {
                    $ids = collect(data_get($this->record->metadata, 'selected_sponsor_ids', []))->filter()->all();

                    return $query->whereKey($ids ?: [0]);
                })
                ->get();
        }

        app(CommunicationService::class)->dispatch($this->record, $students, $sponsors);

        Notification::make()
            ->title("Queued message for {$students->count()} student(s) and {$sponsors->count()} sponsor(s)")
            ->success()
            ->send();
    }
}
