<?php

namespace App\Http\Controllers;

use App\Services\StudentReportService;
use Dompdf\Dompdf;
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

    private function csv($rows, string $filename): StreamedResponse
    {
        return response()->streamDownload(function () use ($rows): void {
            $out = fopen('php://output', 'w');
            fputcsv($out, $this->headings());
            foreach ($rows as $row) {
                fputcsv($out, $this->values($row));
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    private function xlsx($rows, string $filename): StreamedResponse
    {
        return response()->streamDownload(function () use ($rows): void {
            $writer = new Writer();
            $writer->openToFile('php://output');
            $writer->addRow(Row::fromValues($this->headings()));

            foreach ($rows as $row) {
                $writer->addRow(Row::fromValues($this->values($row)));
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
            'Coverage %', 'Accommodation', 'Scholarship Year', 'Scholarship Status', 'GPA', 'CGPA', 'Performance',
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
            $row['gpa'], $row['cgpa'], $row['performance_status'],
        ];
    }
}
