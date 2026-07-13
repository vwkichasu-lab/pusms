<?php

namespace App\Http\Controllers;

use App\Models\Sponsor;
use App\Models\StudentScholarship;
use App\Services\StudentReportService;
use Dompdf\Dompdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use OpenSpout\Writer\XLSX\Writer;
use OpenSpout\Common\Entity\Row;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function students(Request $request, StudentReportService $reports): Response
    {
        $rows = $reports->rows($request->query());

        return response()->view('reports.students', [
            'rows' => $rows,
            'filters' => $request->query(),
            'generatedAt' => now(),
        ]);
    }

    public function exportStudents(string $format, Request $request, StudentReportService $reports): Response|StreamedResponse
    {
        $rows = $reports->rows($request->query());
        $filename = 'pusms-student-report-'.now()->format('Ymd-His');

        return match ($format) {
            'csv' => $this->csv($rows, "{$filename}.csv"),
            'xlsx' => $this->xlsx($rows, "{$filename}.xlsx"),
            'pdf' => $this->pdf($rows, "{$filename}.pdf", $request->query()),
        };
    }

    public function exportSponsors(string $format, Request $request): StreamedResponse
    {
        $rows = Sponsor::query()
            ->with('scholarshipProgrammes.studentScholarships')
            ->when($request->query('status'), fn (Builder $query, string $status): Builder => $query->where('status', $status))
            ->when($request->query('scholarship_stage'), function (Builder $query, string $stage): Builder {
                return $query->whereHas('scholarshipProgrammes.studentScholarships', fn (Builder $scholarship): Builder => $scholarship->where('scholarship_stage', $stage));
            })
            ->orderBy('name')
            ->get()
            ->map(function (Sponsor $sponsor): array {
                $awards = $sponsor->scholarshipProgrammes->flatMap->studentScholarships;

                return [
                    'name' => $sponsor->name,
                    'type' => $sponsor->sponsor_type,
                    'contact_person' => $sponsor->contact_person,
                    'email' => $sponsor->email,
                    'phone' => $sponsor->phone,
                    'status' => $sponsor->status,
                    'programmes' => $sponsor->scholarshipProgrammes->count(),
                    'new_awards' => $awards->where('scholarship_stage', StudentScholarship::STAGE_NEW_AWARD)->count(),
                    'existing_beneficiaries' => $awards->where('scholarship_stage', StudentScholarship::STAGE_EXISTING_BENEFICIARY)->count(),
                ];
            });

        $filename = 'pusms-sponsor-list-'.now()->format('Ymd-His');

        return match ($format) {
            'csv' => $this->csvWithHeadings($rows, "{$filename}.csv", $this->sponsorHeadings(), fn (array $row): array => $this->sponsorValues($row)),
            'xlsx' => $this->xlsxWithHeadings($rows, "{$filename}.xlsx", $this->sponsorHeadings(), fn (array $row): array => $this->sponsorValues($row)),
        };
    }

    private function csv($rows, string $filename): StreamedResponse
    {
        return $this->csvWithHeadings($rows, $filename, $this->headings(), fn (array $row): array => $this->values($row));
    }

    private function csvWithHeadings($rows, string $filename, array $headings, callable $values): StreamedResponse
    {
        return response()->streamDownload(function () use ($rows, $headings, $values): void {
            $out = fopen('php://output', 'w');
            fputcsv($out, $headings);
            foreach ($rows as $row) {
                fputcsv($out, $values($row));
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    private function xlsx($rows, string $filename): StreamedResponse
    {
        return $this->xlsxWithHeadings($rows, $filename, $this->headings(), fn (array $row): array => $this->values($row));
    }

    private function xlsxWithHeadings($rows, string $filename, array $headings, callable $values): StreamedResponse
    {
        return response()->streamDownload(function () use ($rows, $headings, $values): void {
            $writer = new Writer();
            $writer->openToFile('php://output');
            $writer->addRow(Row::fromValues($headings));

            foreach ($rows as $row) {
                $writer->addRow(Row::fromValues($values($row)));
            }

            $writer->close();
        }, $filename, ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']);
    }

    private function pdf($rows, string $filename, array $filters): Response
    {
        $html = view('reports.students-pdf', [
            'rows' => $rows,
            'filters' => $filters,
            'generatedAt' => now(),
        ])->render();

        $dompdf = new Dompdf(['isRemoteEnabled' => true]);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        return response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    /**
     * @return array<int, string>
     */
    private function headings(): array
    {
        return [
            'Student ID', 'Name', 'Email', 'Phone', 'Programme', 'Department', 'School', 'Level', 'Status',
            'Alumni Status', 'Alumni Badge', 'Completion Year', 'Region', 'District', 'Scholarship', 'Sponsor',
            'Coverage %', 'Accommodation', 'Scholarship Year', 'Scholarship Status', 'Scholarship Stage', 'GPA', 'CGPA', 'Performance',
        ];
    }

    /**
     * @return array<int, mixed>
     */
    private function values(array $row): array
    {
        return [
            $row['student_id'], $row['name'], $row['email'], $row['phone'], $row['programme'], $row['department'],
            $row['school'], $row['level'], $row['status'], $row['alumni_status'], $row['alumni_badge'],
            $row['completion_year'], $row['region'], $row['district'], $row['scholarship'], $row['sponsor'],
            $row['coverage_percentage'], $row['accommodation'], $row['scholarship_year'], $row['scholarship_status'],
            $row['scholarship_stage'], $row['gpa'], $row['cgpa'], $row['performance_status'],
        ];
    }

    /**
     * @return array<int, string>
     */
    private function sponsorHeadings(): array
    {
        return [
            'Sponsor', 'Type', 'Contact Person', 'Email', 'Phone', 'Status', 'Programmes',
            'Newly Awarded Students', 'Existing Beneficiaries',
        ];
    }

    /**
     * @return array<int, mixed>
     */
    private function sponsorValues(array $row): array
    {
        return [
            $row['name'], $row['type'], $row['contact_person'], $row['email'], $row['phone'], $row['status'],
            $row['programmes'], $row['new_awards'], $row['existing_beneficiaries'],
        ];
    }
}
