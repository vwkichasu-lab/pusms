<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SystemAssistantController
{
    public function __invoke(Request $request)
    {
        $data = $request->validate([
            'message' => ['required', 'string', 'max:2000'],
            'page' => ['nullable', 'string', 'max:255'],
        ]);

        $prompt = trim($data['message']);
        $page = $data['page'] ?? '/admin/send-email';

        if ($this->isPlaceholderQuestion($prompt)) {
            return response()->json([
                'answer' => $this->placeholderHelp(),
                'subject' => null,
                'generated_message' => null,
            ]);
        }

        $draft = $this->generateDraft($prompt, $page);

        return response()->json([
            'answer' => "Subject: {$draft['subject']}\n\n{$draft['body']}",
            'subject' => $draft['subject'],
            'generated_message' => $draft['body'],
        ]);
    }

    private function generateDraft(string $prompt, string $page): array
    {
        $lower = mb_strtolower($prompt);
        $isSms = str_contains($page, 'send-sms') || str_contains($lower, 'sms') || str_contains($lower, 'text message');
        $isSponsor = str_contains($lower, 'sponsor') || str_contains($lower, 'contact person');
        $name = $isSponsor ? '{{contact_person}}' : '{{student_name}}';
        $organisation = $isSponsor ? '{{sponsor_name}}' : 'Pentecost University';

        $subject = $this->subjectFor($lower, $isSms);
        $purpose = $this->purposeLine($prompt);

        if ($isSms) {
            return [
                'subject' => 'SMS Message',
                'body' => "Dear {$name}, {$purpose} Kindly contact the Scholarship Office if you need assistance.",
            ];
        }

        return [
            'subject' => $subject,
            'body' => "Dear {$name},\n\n{$purpose}\n\nThis message is from the Pentecost University Scholarship Office. Kindly respond if you need clarification or if any detail requires correction.\n\nRegards,\nScholarship Office\n{$organisation}",
        ];
    }

    private function subjectFor(string $prompt, bool $isSms): string
    {
        if ($isSms) {
            return 'SMS Message';
        }

        return match (true) {
            str_contains($prompt, 'meeting') => 'Scholarship Meeting Notice',
            str_contains($prompt, 'award') || str_contains($prompt, 'congrat') => 'Scholarship Award Notice',
            str_contains($prompt, 'renew') => 'Scholarship Renewal Notice',
            str_contains($prompt, 'result') || str_contains($prompt, 'gpa') => 'Scholarship Academic Performance Update',
            str_contains($prompt, 'document') || str_contains($prompt, 'submit') => 'Scholarship Document Submission Request',
            str_contains($prompt, 'deadline') => 'Scholarship Deadline Reminder',
            default => 'Pentecost University Scholarship Update',
        };
    }

    private function purposeLine(string $prompt): string
    {
        $clean = trim(preg_replace('/\s+/', ' ', $prompt) ?? $prompt);
        $clean = preg_replace('/^(write|generate|draft|compose|create)\s+(an?\s+)?(email|sms|message|text message)\s*(for|to)?\s*/i', '', $clean) ?? $clean;

        if ($clean === '') {
            return 'Please take note of this important scholarship update.';
        }

        return 'Please take note: '.$clean.'.';
    }

    private function isPlaceholderQuestion(string $prompt): bool
    {
        $lower = mb_strtolower($prompt);

        return str_contains($lower, '{{') ||
            str_contains($lower, 'placeholder') ||
            str_contains($lower, 'variable') ||
            str_contains($lower, 'what is');
    }

    private function placeholderHelp(): string
    {
        return "Placeholders are replaced automatically for every recipient.\n\nStudents:\n{{student_name}}, {{first_name}}, {{student_id}}, {{programme}}, {{level}}, {{academic_year}}, {{scholarship_name}}\n\nSponsors:\n{{contact_person}}, {{sponsor_name}}\n\nGeneral:\n{{name}}, {{recipient_name}}\n\nExample: Dear {{student_name}} becomes Dear Victor Wugajah Kichasu for Victor.";
    }
}
