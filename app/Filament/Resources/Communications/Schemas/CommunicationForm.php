<?php

namespace App\Filament\Resources\Communications\Schemas;

use App\Models\ScholarshipProgramme;
use App\Models\Sponsor;
use App\Models\Student;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class CommunicationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Message')
                    ->description('Variables: {{student_name}}, {{student_id}}, {{level}}, {{programme}}.')
                    ->schema([
                        Select::make('communication_type')
                            ->required()
                            ->options(['email' => 'Email', 'sms' => 'SMS', 'email_sms' => 'Email and SMS'])
                            ->default('email'),
                        Select::make('metadata.recipient_group')
                            ->label('Send To')
                            ->required()
                            ->live()
                            ->options([
                                'students' => 'Students',
                                'sponsors' => 'Sponsors',
                                'students_and_sponsors' => 'Students and Sponsors',
                            ])
                            ->default('students'),
                        TextInput::make('subject')
                            ->maxLength(255)
                            ->required(fn (Get $get): bool => in_array($get('communication_type'), ['email', 'email_sms'], true)),
                        FileUpload::make('attachment_path')
                            ->label('Attachment')
                            ->disk('public')
                            ->directory('communication-attachments')
                            ->visibility('public')
                            ->storeFileNamesIn('attachment_original_name')
                            ->downloadable()
                            ->openable()
                            ->columnSpanFull(),
                        Textarea::make('message')->required()->rows(8)->columnSpanFull(),
                    ])
                    ->columns(2),
                Section::make('Recipient Filters')
                    ->schema([
                        Select::make('metadata.scholarship_type')
                            ->label('Type Of Scholarship')
                            ->options([
                                'pu_bursary' => 'PU Bursary',
                                'area' => 'Area Scholarship',
                                'copcef' => 'COPCEF',
                                'sponsor' => 'Institution / Sponsor Scholarship',
                                'other' => 'Other',
                            ])
                            ->searchable()
                            ->live(),
                        Select::make('metadata.scholarship_programme_id')
                            ->label('Scholarship Name')
                            ->options(fn (Get $get): array => ScholarshipProgramme::query()
                                ->when($get('metadata.scholarship_type'), fn ($query, $type) => $query->where('scholarship_type', $type))
                                ->orderBy('name')
                                ->pluck('name', 'id')
                                ->all())
                            ->searchable()
                            ->live(),
                        Select::make('metadata.sponsor_id')->label('Sponsor')->options(fn () => Sponsor::query()->orderBy('name')->pluck('name', 'id'))->searchable(),
                        Select::make('metadata.alumni_status')->label('Student Type')->options(['not_alumni' => 'Continuing Student', 'alumni' => 'Alumni']),
                        Toggle::make('metadata.send_all_students')
                            ->label('Send to all listed students')
                            ->default(true)
                            ->live()
                            ->visible(fn (Get $get): bool => in_array($get('metadata.recipient_group'), ['students', 'students_and_sponsors'], true)),
                        Toggle::make('metadata.send_all_sponsors')
                            ->label('Send to all listed sponsors')
                            ->default(true)
                            ->live()
                            ->visible(fn (Get $get): bool => in_array($get('metadata.recipient_group'), ['sponsors', 'students_and_sponsors'], true)),
                        CheckboxList::make('metadata.selected_student_ids')
                            ->label('Students Matching The Scholarship Filter')
                            ->options(fn (Get $get): array => Student::query()
                                ->with(['scholarships.scholarshipProgramme'])
                                ->when($get('metadata.alumni_status'), fn ($query, $status) => $query->where('alumni_status', $status))
                                ->when($get('metadata.scholarship_type'), fn ($query, $type) => $query->whereHas('scholarships.scholarshipProgramme', fn ($scholarship) => $scholarship->where('scholarship_type', $type)))
                                ->when($get('metadata.scholarship_programme_id'), fn ($query, $id) => $query->whereHas('scholarships', fn ($scholarship) => $scholarship->where('scholarship_programme_id', $id)))
                                ->orderBy('student_id')
                                ->limit(250)
                                ->get()
                                ->mapWithKeys(fn (Student $student): array => [
                                    $student->id => "{$student->student_id} | {$student->full_name} | {$student->phone} | {$student->email}",
                                ])
                                ->all())
                            ->searchable()
                            ->columns(1)
                            ->bulkToggleable()
                            ->columnSpanFull()
                            ->visible(fn (Get $get): bool => in_array($get('metadata.recipient_group'), ['students', 'students_and_sponsors'], true) && ! (bool) $get('metadata.send_all_students')),
                        CheckboxList::make('metadata.selected_sponsor_ids')
                            ->label('Sponsors / Contact Persons')
                            ->options(fn (): array => Sponsor::query()
                                ->orderBy('name')
                                ->get()
                                ->mapWithKeys(fn (Sponsor $sponsor): array => [
                                    $sponsor->id => "{$sponsor->name} | {$sponsor->contact_person} | {$sponsor->phone} | {$sponsor->email}",
                                ])
                                ->all())
                            ->searchable()
                            ->columns(1)
                            ->bulkToggleable()
                            ->columnSpanFull()
                            ->visible(fn (Get $get): bool => in_array($get('metadata.recipient_group'), ['sponsors', 'students_and_sponsors'], true) && ! (bool) $get('metadata.send_all_sponsors')),
                    ])
                    ->columns(3),
            ]);
    }
}
