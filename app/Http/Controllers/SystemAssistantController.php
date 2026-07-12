<?php

namespace App\Http\Controllers;

use App\Models\CommunicationRecipient;
use App\Models\ScholarshipProgramme;
use App\Models\Student;
use App\Models\StudentScholarship;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SystemAssistantController
{
    public function __invoke(Request $request)
    {
        $data = $request->validate([
            'message' => ['required', 'string', 'max:2000'],
            'page' => ['nullable', 'string', 'max:255'],
        ]);

        $question = mb_strtolower($data['message']);
        $page = $data['page'] ?? '/admin';
        [$answer, $url, $navigate] = $this->answer($question, $page);

        return response()->json([
            'answer' => $answer,
            'url' => $url,
            'navigate' => $navigate,
        ]);
    }

    /**
     * @return array{0: string, 1: string|null, 2: bool}
     */
    private function answer(string $question, string $page): array
    {
        if ((str_contains($question, 'draft') || str_contains($question, 'write') || str_contains($question, 'compose')) && str_contains($question, 'email')) {
            return [
                "Subject: Scholarship Update\n\nDear {{student_name}},\n\nPlease check your Pentecost University scholarship update and contact the Scholarship Office if any detail needs correction.\n\nThank you.",
                '/admin/send-email',
                false,
            ];
        }

        if ((str_contains($question, 'draft') || str_contains($question, 'write') || str_contains($question, 'compose')) && (str_contains($question, 'sms') || str_contains($question, 'whatsapp'))) {
            return [
                "Dear {{student_name}}, please check your Pentecost University scholarship update and contact the Scholarship Office if you need help.",
                '/admin/send-sms',
                false,
            ];
        }

        $location = $this->locationAnswer($question);

        if ($location) {
            $navigate = str_contains($question, 'go to') || str_contains($question, 'open') || str_contains($question, 'take me');

            return [
                $navigate ? "Opening {$location['label']}." : "{$location['label']} is here: {$location['path']}. Use the sidebar group: {$location['group']}.",
                $location['path'],
                $navigate,
            ];
        }

        if (str_contains($question, '{{') || str_contains($question, 'variable') || str_contains($question, 'placeholder')) {
            return [
                "{{student_name}} and similar words are placeholders. When you send a bulk message, PUSMS replaces them for each person. Example: Dear {{student_name}} becomes Dear Victor Wugajah Kichasu for Victor, and Dear Ama Serwaa Boateng for Ama. Use them when one message must feel personal to every recipient.",
                '/admin/send-email',
                false,
            ];
        }

        if (str_contains($question, 'email') || str_contains($question, 'message')) {
            return [
                "Go to Messaging > Send Email.\n\nDraft:\nSubject: Scholarship Update\n\nDear {{student_name}},\n\nPlease check your Pentecost University scholarship update and contact the Scholarship Office if any detail needs correction.\n\nThank you.",
                '/admin/send-email',
                false,
            ];
        }

        if (str_contains($question, 'sms') || str_contains($question, 'whatsapp')) {
            return [
                "Go to Messaging > Send SMS.\n\nSMS draft:\nDear {{student_name}}, please check your Pentecost University scholarship update and contact the Scholarship Office if you need help.",
                '/admin/send-sms',
                false,
            ];
        }

        if (str_contains($question, 'student') || str_contains($question, 'find')) {
            $studentCount = Student::query()->count();

            return [
                "There are {$studentCount} student records. To find one quickly, go to Students > Global Student Search or use the search box on Students.",
                '/admin/global-student-search',
                false,
            ];
        }

        if (str_contains($question, 'scholarship') || str_contains($question, 'award')) {
            $awards = StudentScholarship::query()->count();
            $types = ScholarshipProgramme::query()->count();
            $spend = number_format((float) StudentScholarship::query()->sum('amount_awarded'), 2);

            return [
                "The system has {$types} scholarship types and {$awards} student scholarship awards. Total recorded amount awarded is GHS {$spend}. Manage types at Scholarships > Types Of Scholarship. Assign students at Scholarships > Scholarship Students.",
                '/admin/student-scholarships',
                false,
            ];
        }

        if (str_contains($question, 'dashboard') || str_contains($question, 'chart')) {
            return [
                'The dashboard charts show scholarship spend, percentage coverage, student growth/drop, students by level, faculty, region, and scholarship type.',
                '/admin',
                false,
            ];
        }

        if (str_contains($question, 'where') || str_contains($question, 'page')) {
            return [
                "You are currently on {$page}. Ask for the exact thing, for example: where is alumni, where is send email, where is backup.",
                null,
                false,
            ];
        }

        $sent = CommunicationRecipient::query()->where('delivery_status', 'sent')->count();
        $user = Auth::user()?->name ?? 'user';

        return [
            "Hello {$user}. I am PUSMS AI. Ask me directly, for example: where is students, draft email, go to reports, explain {{student_name}}, or show scholarship awards. Current page: {$page}. Sent messages: {$sent}.",
            null,
            false,
        ];
    }

    /**
     * @return array{label: string, path: string, group: string}|null
     */
    private function locationAnswer(string $question): ?array
    {
        $pages = [
            ['keys' => ['dashboard', 'home'], 'label' => 'Dashboard', 'path' => '/admin', 'group' => 'Dashboard'],
            ['keys' => ['student report', 'reports'], 'label' => 'Student Reports', 'path' => '/admin/student-reports', 'group' => 'Reports'],
            ['keys' => ['letter', 'scholarship letter'], 'label' => 'Scholarship Letter Generator', 'path' => '/admin/scholarship-letter-generator', 'group' => 'Reports'],
            ['keys' => ['generated letter'], 'label' => 'Generated Letters', 'path' => '/admin/generated-letters', 'group' => 'Reports'],
            ['keys' => ['send email', 'email'], 'label' => 'Send Email', 'path' => '/admin/send-email', 'group' => 'Messaging'],
            ['keys' => ['send sms', 'sms'], 'label' => 'Send SMS', 'path' => '/admin/send-sms', 'group' => 'Messaging'],
            ['keys' => ['gmail', 'gmail settings'], 'label' => 'Gmail Settings', 'path' => '/admin/gmail-settings', 'group' => 'Messaging'],
            ['keys' => ['inbox'], 'label' => 'Inbox', 'path' => '/admin/gmail-inbox', 'group' => 'Messaging'],
            ['keys' => ['message history', 'history'], 'label' => 'Message History', 'path' => '/admin/message-history', 'group' => 'Messaging'],
            ['keys' => ['team message', 'notification'], 'label' => 'Team Messages', 'path' => '/admin/team-messages', 'group' => 'Messaging'],
            ['keys' => ['student list', 'students'], 'label' => 'Students', 'path' => '/admin/students', 'group' => 'Students'],
            ['keys' => ['alumni'], 'label' => 'Alumni', 'path' => '/admin/alumni-students', 'group' => 'Students'],
            ['keys' => ['global search', 'find student'], 'label' => 'Global Student Search', 'path' => '/admin/global-student-search', 'group' => 'Students'],
            ['keys' => ['student import', 'bulk student'], 'label' => 'Student Import Logs', 'path' => '/admin/student-imports', 'group' => 'Students'],
            ['keys' => ['result import'], 'label' => 'Result Imports', 'path' => '/admin/result-imports', 'group' => 'Students'],
            ['keys' => ['student result', 'results'], 'label' => 'Student Results', 'path' => '/admin/student-results', 'group' => 'Students'],
            ['keys' => ['level migration', 'migration'], 'label' => 'Level Migration History', 'path' => '/admin/student-level-progressions', 'group' => 'Students'],
            ['keys' => ['scholarship type', 'types of scholarship'], 'label' => 'Types Of Scholarship', 'path' => '/admin/scholarship-programmes', 'group' => 'Scholarships'],
            ['keys' => ['scholarship student', 'awards'], 'label' => 'Scholarship Students', 'path' => '/admin/student-scholarships', 'group' => 'Scholarships'],
            ['keys' => ['sponsor'], 'label' => 'Sponsors', 'path' => '/admin/sponsors', 'group' => 'Scholarships'],
            ['keys' => ['faculty', 'school'], 'label' => 'Faculty', 'path' => '/admin/schools', 'group' => 'Academic Management'],
            ['keys' => ['department'], 'label' => 'Departments', 'path' => '/admin/departments', 'group' => 'Academic Management'],
            ['keys' => ['programme', 'program'], 'label' => 'Programmes', 'path' => '/admin/programmes', 'group' => 'Academic Management'],
            ['keys' => ['level'], 'label' => 'Levels', 'path' => '/admin/levels', 'group' => 'Academic Management'],
            ['keys' => ['academic year'], 'label' => 'Academic Years', 'path' => '/admin/academic-years', 'group' => 'Academic Management'],
            ['keys' => ['semester'], 'label' => 'Semesters', 'path' => '/admin/semesters', 'group' => 'Academic Management'],
            ['keys' => ['user'], 'label' => 'Users', 'path' => '/admin/users', 'group' => 'System'],
            ['keys' => ['backup', 'restore', 'recycle'], 'label' => 'Backup & Restore', 'path' => '/admin/backup-restore', 'group' => 'System'],
            ['keys' => ['general settings', 'settings'], 'label' => 'General Settings', 'path' => '/admin/general-settings', 'group' => 'System'],
        ];

        foreach ($pages as $page) {
            foreach ($page['keys'] as $key) {
                if (str_contains($question, $key)) {
                    return [
                        'label' => $page['label'],
                        'path' => $page['path'],
                        'group' => $page['group'],
                    ];
                }
            }
        }

        return null;
    }
}
