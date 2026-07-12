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

        return response()->json([
            'answer' => $this->answer($question, $page),
        ]);
    }

    private function answer(string $question, string $page): string
    {
        if (str_contains($question, 'email') || str_contains($question, 'message')) {
            return "You can send email from Messaging > Send Email after Gmail is connected in Gmail Settings. Draft you can use:\n\nSubject: Scholarship Update\n\nDear {{student_name}},\n\nWe are writing from the Pentecost University Scholarship Office regarding your scholarship record. Please check your details and respond if any information needs correction.\n\nThank you.";
        }

        if (str_contains($question, 'sms') || str_contains($question, 'whatsapp')) {
            return "For SMS, use Messaging > Send SMS. For WhatsApp, prepare a short message and send it through the student's phone number. Suggested text:\n\nDear {{student_name}}, please check your Pentecost University scholarship update and contact the Scholarship Office if you need help.";
        }

        if (str_contains($question, 'student') || str_contains($question, 'find')) {
            $studentCount = Student::query()->count();

            return "There are {$studentCount} student records. To find a student, use the header search, Students page search, or Students > Global Student Search. Current page: {$page}.";
        }

        if (str_contains($question, 'scholarship') || str_contains($question, 'award')) {
            $awards = StudentScholarship::query()->count();
            $types = ScholarshipProgramme::query()->count();
            $spend = number_format((float) StudentScholarship::query()->sum('amount_awarded'), 2);

            return "The system has {$types} scholarship types and {$awards} student scholarship awards. Total recorded amount awarded is GHS {$spend}. Use Scholarships > Types Of Scholarship to manage scholarship types and Scholarships > Scholarship Students to assign awards.";
        }

        if (str_contains($question, 'dashboard') || str_contains($question, 'chart')) {
            return 'The dashboard shows student counts, scholarship spending, scholarship percentage distribution, student growth, levels, regions, faculties, and scholarship type summaries. Use these charts to report yearly spend and beneficiary growth.';
        }

        if (str_contains($question, 'where') || str_contains($question, 'page')) {
            return "You are currently on {$page}. If you cannot find a feature, try the sidebar groups: Messaging, Reports, Scholarships, Students, Academic Management, and System.";
        }

        $sent = CommunicationRecipient::query()->where('delivery_status', 'sent')->count();
        $user = Auth::user()?->name ?? 'user';

        return "Hello {$user}. I can help with PUSMS navigation, scholarship records, student search, reports, email/SMS drafts, and dashboard interpretation. Current page: {$page}. Messages successfully sent so far: {$sent}.";
    }
}
