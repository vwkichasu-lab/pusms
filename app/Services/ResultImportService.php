<?php

namespace App\Services;

use App\Models\AcademicYear;
use App\Models\ResultImport;
use App\Models\Student;
use App\Models\StudentResult;
use OpenSpout\Reader\CSV\Reader as CsvReader;
use OpenSpout\Reader\XLSX\Reader as XlsxReader;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ResultImportService
{
    private const REQUIRED_HEADERS = ['student_id', 'academic_year', 'gpa'];

    /**
     * @return array{valid:int, invalid:int, rows:array<int, array<string, mixed>>, errors:array<int, string>}
     */
    public function preview(ResultImport $import): array
    {
        $rows = $this->readRows(Storage::disk('local')->path($import->stored_path));
        $headers = array_map(fn (string $header): string => Str::snake(trim($header)), array_shift($rows) ?? []);
        $missing = array_diff(self::REQUIRED_HEADERS, $headers);

        if ($missing !== []) {
            $errors = ['Missing required columns: '.implode(', ', $missing)];
            $import->update(['status' => 'invalid', 'errors' => $errors]);

            return ['valid' => 0, 'invalid' => count($rows), 'rows' => [], 'errors' => $errors];
        }

        $preview = [];
        $errors = [];
        $seen = [];

        foreach ($rows as $index => $row) {
            if ($row === [] || count(array_filter($row, fn ($value): bool => filled($value))) === 0) {
                continue;
            }

            $line = $index + 2;
            $data = array_combine($headers, array_pad($row, count($headers), null));
            $studentId = trim((string) ($data['student_id'] ?? $data['index_number'] ?? ''));
            $academicYear = trim((string) ($data['academic_year'] ?? ''));
            $key = "{$studentId}|{$academicYear}";
            $rowErrors = [];

            $student = Student::query()->where('student_id', $studentId)->first();
            if (! $student) {
                $rowErrors[] = 'Unmatched student';
            }

            if (isset($seen[$key])) {
                $rowErrors[] = 'Duplicate row in import file';
            }
            $seen[$key] = true;

            if ($student && StudentResult::query()
                ->where('student_id', $student->id)
                ->whereHas('academicYear', fn ($query) => $query->where('name', $academicYear))
                ->exists()) {
                $rowErrors[] = 'Duplicate existing result';
            }

            if (! is_numeric($data['gpa'] ?? null)) {
                $rowErrors[] = 'Invalid GPA';
            }

            $status = $rowErrors === [] ? 'valid' : 'invalid';
            if ($status === 'invalid') {
                $errors[] = "Row {$line}: ".implode(', ', $rowErrors);
            }

            $preview[] = [
                'line' => $line,
                'status' => $status,
                'errors' => $rowErrors,
                'data' => $data,
            ];
        }

        $valid = collect($preview)->where('status', 'valid')->count();
        $invalid = count($preview) - $valid;

        $import->update([
            'status' => $invalid > 0 ? 'preview_with_errors' : 'preview_ready',
            'total_rows' => count($preview),
            'valid_rows' => $valid,
            'invalid_rows' => $invalid,
            'preview_rows' => array_slice($preview, 0, 100),
            'errors' => $errors,
        ]);

        return ['valid' => $valid, 'invalid' => $invalid, 'rows' => $preview, 'errors' => $errors];
    }

    public function confirm(ResultImport $import): int
    {
        $preview = $import->preview_rows ?? [];
        $created = 0;

        foreach ($preview as $row) {
            if (($row['status'] ?? null) !== 'valid') {
                continue;
            }

            $data = $row['data'];
            $student = Student::query()->with(['programme', 'level'])->where('student_id', trim((string) $data['student_id']))->first();
            $academicYear = AcademicYear::firstOrCreate(['name' => trim((string) $data['academic_year'])]);

            if (! $student) {
                continue;
            }

            StudentResult::updateOrCreate(
                [
                    'student_id' => $student->id,
                    'academic_year_id' => $academicYear->id,
                    'semester_id' => null,
                ],
                [
                    'index_number' => $data['index_number'] ?? null,
                    'programme_snapshot' => $student->programme?->name,
                    'level_snapshot' => $student->level?->name,
                    'credit_hours' => blank($data['credit_hours'] ?? null) ? null : (int) $data['credit_hours'],
                    'grade_point' => blank($data['grade_point'] ?? null) ? null : (float) $data['grade_point'],
                    'score' => blank($data['score'] ?? null) ? null : (float) $data['score'],
                    'gpa' => (float) $data['gpa'],
                    'cgpa' => null,
                    'performance_status' => $data['performance_status'] ?? 'satisfactory',
                    'data_source' => 'Academic Department Import',
                    'created_or_imported_at' => now(),
                    'recorded_by' => Auth::id(),
                ],
            );

            $created++;
        }

        $import->update(['status' => 'completed']);

        return $created;
    }

    /**
     * @return array<int, array<int, mixed>>
     */
    private function readRows(string $path): array
    {
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $reader = $extension === 'xlsx' ? new XlsxReader() : new CsvReader();
        $reader->open($path);
        $rows = [];

        foreach ($reader->getSheetIterator() as $sheet) {
            foreach ($sheet->getRowIterator() as $row) {
                $rows[] = $row->toArray();
            }
            break;
        }

        $reader->close();

        return $rows;
    }
}
