<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Contracts\View\View;

class ScholarshipHistoryController extends Controller
{
    public function show(Student $student): View
    {
        $student->load([
            'programme.department.school',
            'level',
            'scholarships' => fn ($query) => $query
                ->with(['scholarshipProgramme.sponsor', 'academicYear', 'semester', 'approver'])
                ->latest('award_date')
                ->latest(),
            'results' => fn ($query) => $query
                ->with(['academicYear', 'semester'])
                ->latest(),
        ]);

        return view('reports.scholarship-history', [
            'student' => $student,
            'scholarships' => $student->scholarships,
            'results' => $student->results,
        ]);
    }
}
