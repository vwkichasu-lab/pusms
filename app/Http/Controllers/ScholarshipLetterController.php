<?php

namespace App\Http\Controllers;

use App\Models\StudentScholarship;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ScholarshipLetterController extends Controller
{
    public function show(Request $request, StudentScholarship $award): View
    {
        $award->load(['student.programme', 'scholarshipProgramme', 'academicYear']);
        abort_unless($award->scholarshipProgramme?->scholarship_type === 'pu_bursary', 404);

        $student = $award->student;
        $academicYear = $award->academicYear?->name ?? now()->format('Y').'/'.now()->addYear()->format('Y');
        $percentage = number_format((float) $award->coverage_percentage, 0);
        $accommodation = $award->covers_accommodation ? 'including accommodation' : 'excluding hostel fees';

        $defaultBody = "Congratulations!\n\n"
            ."I am glad to inform you that you have been awarded {$percentage}% {$award->scholarshipProgramme?->name} support on your fees ({$accommodation}) for the {$academicYear} Academic Year.\n\n"
            ."Please, note that this scholarship support is renewable yearly, for which you are expected to reapply each academic year.\n\n"
            ."The scholarship's continuity depends on your academic performance, which will be checked and advised by the scholarship board.\n\n"
            ."As a beneficiary of the award, you are expected to meet the outlined expectations below;\n"
            ."- Excellent academic performance\n"
            ."- Being good ambassadors of PU\n"
            ."- Work for PU on a subsidized rate\n"
            ."- Be disciplined individuals and have a good moral standard\n"
            ."- Promote and support PU recruitment activities\n\n"
            ."Also, you are required to submit a short report to the Committee on how beneficial the scholarship support has been to you at the end of every semester.\n\n"
            ."Please, indicate your acceptance or otherwise to the Chairman of the Scholarship Committee within a week of receipt of this letter.\n\n"
            ."I wish you well in your studies.";

        $signaturePath = $request->input('signature_path');

        if ($request->hasFile('signature')) {
            $signaturePath = $request->file('signature')->store('letter-signatures', 'public');
        }

        return view('letters.scholarship-award', [
            'award' => $award,
            'student' => $student,
            'reference' => $request->input('reference', 'PU/BA/'.str_pad((string) $award->id, 3, '0', STR_PAD_LEFT).'/'.now()->format('m/y')),
            'letterDate' => $request->input('letter_date', now()->format('l, F j, Y')),
            'body' => $request->input('body', $defaultBody),
            'signatoryName' => $request->input('signatory_name', 'REV. AUGUSTINE ARTHUR-NORMAN'),
            'signatoryTitle' => $request->input('signatory_title', 'SCHOLARSHIP COORDINATOR'),
            'signaturePath' => $signaturePath,
            'signatureUrl' => $signaturePath ? Storage::disk('public')->url($signaturePath) : null,
        ]);
    }
}
