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
        $purpose = $this->cleanPurpose($prompt);

        if ($isSms) {
            return [
                'subject' => $subject,
                'body' => "Dear {$name}, {$purpose} Kindly contact the Scholarship Office if you need clarification. Thank you.",
            ];
        }

        return [
            'subject' => $subject,
            'body' => "Dear {$name},\n\n{$purpose}\n\nKindly take note and respond to the Scholarship Office if you need clarification.\n\nRegards,\nScholarship Office\nPentecost University",
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
