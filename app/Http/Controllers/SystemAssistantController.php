<?php

namespace App\Http\Controllers;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class SystemAssistantController
{
    public function __invoke(Request $request)
    {
        $data = $request->validate([
            'message' => ['required', 'string', 'max:3000'],
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

        $draft = $this->generateWithOpenAI($prompt, $page) ?? $this->generateLocalDraft($prompt, $page);

        return response()->json([
            'answer' => "Subject: {$draft['subject']}\n\n{$draft['body']}",
            'subject' => $draft['subject'],
            'generated_message' => $draft['body'],
        ]);
    }

    private function generateWithOpenAI(string $prompt, string $page): ?array
    {
        $apiKey = (string) config('services.openai.api_key');

        if (blank($apiKey)) {
            return null;
        }

        $isSms = str_contains($page, 'send-sms') || Str::of($prompt)->lower()->contains(['sms', 'text message']);
        $channel = $isSms ? 'SMS' : 'email';

        try {
            $response = Http::withToken($apiKey)
                ->timeout(25)
                ->acceptJson()
                ->post(rtrim((string) config('services.openai.base_url'), '/').'/chat/completions', [
                    'model' => config('services.openai.model', 'gpt-4o-mini'),
                    'temperature' => 0.65,
                    'response_format' => ['type' => 'json_object'],
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => $this->systemPrompt($channel),
                        ],
                        [
                            'role' => 'user',
                            'content' => "Current PUSMS page: {$page}\nChannel: {$channel}\nUser request: {$prompt}",
                        ],
                    ],
                ]);
        } catch (ConnectionException) {
            return null;
        }

        if (! $response->successful()) {
            return null;
        }

        $content = Arr::get($response->json(), 'choices.0.message.content');

        if (! is_string($content) || blank($content)) {
            return null;
        }

        $json = json_decode($content, true);

        if (! is_array($json)) {
            return null;
        }

        $subject = trim((string) ($json['subject'] ?? 'Pentecost University Scholarship Update'));
        $body = trim((string) ($json['message'] ?? $json['body'] ?? ''));

        if ($body === '') {
            return null;
        }

        return [
            'subject' => Str::limit($subject, 140, ''),
            'body' => $body,
        ];
    }

    private function systemPrompt(string $channel): string
    {
        return <<<PROMPT
You are PUSMS AI, a focused writing assistant inside Pentecost University Scholarship Management System.
Your only job is to generate ready-to-send {$channel} messages for scholarship students or sponsors.
Return only valid JSON with this shape:
{"subject":"short subject for email or SMS Message for SMS","message":"ready to send message"}

Rules:
- Write warm, clear, professional Pentecost University scholarship communication.
- If the recipient is a student, personalize with {{student_name}}.
- If the recipient is a sponsor or contact person, personalize with {{contact_person}} and use {{sponsor_name}} only when useful.
- For bulk messages, never invent real names. Use placeholders exactly with double curly braces.
- For email, include greeting, clear body, and closing from Scholarship Office, Pentecost University.
- For SMS, keep it concise and suitable for a phone message.
- Do not explain placeholders unless the user asks what placeholders mean.
- Do not say you cannot perform the task. Generate the best message from the request.
PROMPT;
    }

    private function generateLocalDraft(string $prompt, string $page): array
    {
        $lower = mb_strtolower($prompt);
        $isSms = str_contains($page, 'send-sms') || str_contains($lower, 'sms') || str_contains($lower, 'text message');
        $isSponsor = str_contains($lower, 'sponsor') || str_contains($lower, 'contact person') || str_contains($lower, 'donor');
        $name = $isSponsor ? '{{contact_person}}' : '{{student_name}}';
        $subject = $isSms ? 'SMS Message' : $this->subjectFor($lower);
        $body = $this->bodyFor($prompt, $lower, $name, $isSponsor, $isSms);

        if ($isSms) {
            return [
                'subject' => $subject,
                'body' => $body,
            ];
        }

        return [
            'subject' => $subject,
            'body' => $body,
        ];
    }

    private function subjectFor(string $prompt): string
    {
        return match (true) {
            str_contains($prompt, 'meeting') => 'Scholarship Meeting Notice',
            str_contains($prompt, 'award') || str_contains($prompt, 'congrat') => 'Scholarship Award Notice',
            str_contains($prompt, 'renew') => 'Scholarship Renewal Notice',
            str_contains($prompt, 'result') || str_contains($prompt, 'gpa') => 'Scholarship Academic Performance Update',
            str_contains($prompt, 'document') || str_contains($prompt, 'submit') => 'Scholarship Document Submission Request',
            str_contains($prompt, 'deadline') => 'Scholarship Deadline Reminder',
            str_contains($prompt, 'interview') => 'Scholarship Interview Notice',
            default => 'Pentecost University Scholarship Update',
        };
    }

    private function bodyFor(string $prompt, string $lower, string $name, bool $isSponsor, bool $isSms): string
    {
        if ($isSms) {
            return $this->smsBodyFor($prompt, $lower, $name);
        }

        $closing = $isSponsor
            ? "Thank you for your continued support.\n\nRegards,\nScholarship Office\nPentecost University"
            : "Please treat this notice as important.\n\nThank you.\n\nPentecost University Scholarship Committee";

        if (str_contains($lower, 'meeting') || str_contains($lower, 'vc') || str_contains($lower, 'vice chancellor')) {
            return "Dear {$name},\n\nYou are kindly informed that there will be an important meeting with the Vice-Chancellor and the Scholarship Committee.\n\nThe meeting is scheduled as follows:\n\nDate: [Insert Date]\nTime: [Insert Time]\nVenue: [Insert Venue]\n\nAll selected scholarship recipients are expected to be present and punctual, as important information concerning scholarship support, expectations, and student welfare will be discussed.\n\n{$closing}";
        }

        if (str_contains($lower, 'award') || str_contains($lower, 'congrat')) {
            return "Dear {$name},\n\nCongratulations. We are pleased to inform you that your scholarship award has been approved for the current academic year.\n\nKindly visit the Scholarship Office for any required confirmation and further guidance on the terms of the award.\n\n{$closing}";
        }

        if (str_contains($lower, 'renew')) {
            return "Dear {$name},\n\nYou are kindly reminded to complete your scholarship renewal process for the current academic year.\n\nPlease submit all required documents before the stated deadline so that your renewal can be reviewed on time.\n\n{$closing}";
        }

        if (str_contains($lower, 'result') || str_contains($lower, 'gpa') || str_contains($lower, 'academic performance')) {
            return "Dear {$name},\n\nThe Scholarship Office is reviewing beneficiaries' academic performance records. Kindly ensure that your latest result or GPA information has been submitted and correctly updated.\n\nStudents whose records are incomplete should contact the Scholarship Office for assistance.\n\n{$closing}";
        }

        if (str_contains($lower, 'document') || str_contains($lower, 'submit')) {
            return "Dear {$name},\n\nYou are kindly requested to submit the required scholarship documents to the Scholarship Office.\n\nRequired documents: [Insert Documents]\nDeadline: [Insert Deadline]\nSubmission point: Scholarship Office\n\nFailure to submit the required documents on time may delay the processing of your scholarship record.\n\n{$closing}";
        }

        if (str_contains($lower, 'deadline')) {
            return "Dear {$name},\n\nThis is a reminder that the deadline for the scholarship requirement is approaching.\n\nDeadline: [Insert Deadline]\nRequirement: [Insert Requirement]\n\nKindly complete the required action before the deadline to avoid delays or further follow-up.\n\n{$closing}";
        }

        if (str_contains($lower, 'interview')) {
            return "Dear {$name},\n\nYou are kindly invited to attend a scholarship interview with the Scholarship Committee.\n\nDate: [Insert Date]\nTime: [Insert Time]\nVenue: [Insert Venue]\n\nPlease be punctual and come along with any required documents.\n\n{$closing}";
        }

        $purpose = $this->cleanPurpose($prompt);

        return "Dear {$name},\n\n{$purpose}\n\nKindly take note and respond to the Scholarship Office if you need clarification.\n\nRegards,\nScholarship Office\nPentecost University";
    }

    private function smsBodyFor(string $prompt, string $lower, string $name): string
    {
        if (str_contains($lower, 'meeting') || str_contains($lower, 'vc') || str_contains($lower, 'vice chancellor')) {
            return "Dear {$name}, you are invited to an important scholarship meeting with the Vice-Chancellor and Scholarship Committee. Date: [Insert Date]. Time: [Insert Time]. Venue: [Insert Venue]. Please be punctual.";
        }

        if (str_contains($lower, 'award') || str_contains($lower, 'congrat')) {
            return "Dear {$name}, congratulations. Your scholarship award has been approved. Please contact the Scholarship Office for confirmation and further guidance.";
        }

        if (str_contains($lower, 'renew')) {
            return "Dear {$name}, kindly complete your scholarship renewal process and submit all required documents before [Insert Deadline].";
        }

        if (str_contains($lower, 'result') || str_contains($lower, 'gpa')) {
            return "Dear {$name}, kindly ensure your latest result or GPA record has been submitted to the Scholarship Office for review.";
        }

        if (str_contains($lower, 'document') || str_contains($lower, 'submit')) {
            return "Dear {$name}, kindly submit the required scholarship documents by [Insert Deadline] at the Scholarship Office.";
        }

        $purpose = $this->cleanPurpose($prompt);

        return "Dear {$name}, {$purpose} Kindly contact the Scholarship Office if you need clarification. Thank you.";
    }

    private function cleanPurpose(string $prompt): string
    {
        $clean = trim(preg_replace('/\s+/', ' ', $prompt) ?? $prompt);
        $clean = preg_replace('/^(write|generate|draft|compose|create|prepare)\s+(an?\s+)?(email|sms|message|text message)\s*(for|to)?\s*/i', '', $clean) ?? $clean;

        if ($clean === '') {
            return 'Please take note of this important scholarship update.';
        }

        $clean = rtrim($clean, ". \t\n\r\0\x0B");

        return ucfirst($clean).'.';
    }

    private function isPlaceholderQuestion(string $prompt): bool
    {
        $lower = mb_strtolower($prompt);

        return str_contains($lower, '{{') ||
            str_contains($lower, 'placeholder') ||
            str_contains($lower, 'variable') ||
            str_contains($lower, 'how do i use');
    }

    private function placeholderHelp(): string
    {
        return "Placeholders are words inside double curly braces. PUSMS replaces them for each selected recipient before sending.\n\nStudents: {{student_name}}, {{first_name}}, {{student_id}}, {{programme}}, {{level}}, {{academic_year}}, {{scholarship_name}}\n\nSponsors: {{contact_person}}, {{sponsor_name}}\n\nGeneral: {{name}}, {{recipient_name}}\n\nExample: Dear {{student_name}} becomes Dear Victor Wugajah Kichasu for Victor, and Dear Ama Serwaa Boaten for Ama.";
    }
}
