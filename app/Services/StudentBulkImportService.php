<?php

namespace App\Services;

use App\Models\Level;
use App\Models\Programme;
use App\Models\ScholarshipProgramme;
use App\Models\Student;
use App\Models\StudentImport;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class StudentBulkImportService
{
    /**
     * @return array{total:int, successful:int, failed:int, errors:array<int, string>}
     */
    public function import(StudentImport $import): array
    {
        $path = Storage::disk('local')->path($import->stored_path);
        $handle = fopen($path, 'r');

        if ($handle === false) {
            throw new \RuntimeException('Unable to open uploaded import file.');
        }

        $headers = fgetcsv($handle);
        $headers = array_map(fn (string $header): string => Str::snake(trim($header)), $headers ?: []);
        $total = 0;
        $successful = 0;
        $errors = [];

        while (($row = fgetcsv($handle)) !== false) {
            $total++;
            $data = array_combine($headers, $row);

            if ($data === false) {
                $errors[] = "Row {$total}: invalid column count.";

                continue;
            }

            try {
                DB::transaction(function () use ($data): void {
                    $programme = $this->resolveProgramme($data);
                    $level = Level::query()->where('numeric_value', (int) ($data['level'] ?? 0))->first();

                    if (! $level) {
                        throw new \InvalidArgumentException('Level was not found. Use values such as 100, 200, 300, or 400.');
                    }

                    Student::updateOrCreate(
                        ['student_id' => trim((string) $data['student_id'])],
                        [
                            'first_name' => trim((string) $data['first_name']),
                            'middle_name' => blank($data['middle_name'] ?? null) ? null : trim((string) $data['middle_name']),
                            'last_name' => trim((string) $data['last_name']),
                            'email' => trim((string) $data['email']),
                            'phone' => trim((string) $data['phone']),
                            'programme_id' => $programme->id,
                            'level_id' => $level->id,
                            'admission_year' => (int) $data['admission_year'],
                            'student_status' => $data['student_status'] ?: 'active',
                            'student_batch' => $data['student_batch'] ?? null,
                            'graduation_year' => blank($data['graduation_year'] ?? null) ? null : (int) $data['graduation_year'],
                            'alumni_status' => $data['alumni_status'] ?: 'not_alumni',
                            'alumni_badge' => $data['alumni_badge'] ?? null,
                            'home_town' => $data['home_town'] ?? null,
                            'district' => $data['district'] ?? null,
                            'region' => $data['region'] ?? null,
                        ],
                    );
                });

                $successful++;
            } catch (\Throwable $exception) {
                $errors[] = "Row {$total}: {$exception->getMessage()}";
            }
        }

        fclose($handle);

        $failed = count($errors);
        $import->update([
            'total_rows' => $total,
            'successful_rows' => $successful,
            'failed_rows' => $failed,
            'errors' => $errors,
            'status' => $failed > 0 ? 'completed_with_errors' : 'completed',
        ]);

        return compact('total', 'successful', 'failed') + ['errors' => $errors];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function resolveProgramme(array $data): Programme
    {
        $code = trim((string) ($data['programme_code'] ?? ''));

        if ($code !== '') {
            $programme = Programme::query()->where('code', $code)->first();

            if ($programme) {
                return $programme;
            }
        }

        $name = trim((string) ($data['programme'] ?? $data['programme_name'] ?? ''));
        $department = $this->normalize((string) ($data['department'] ?? $data['department_name'] ?? ''));
        $school = $this->normalize((string) ($data['school'] ?? $data['school_name'] ?? ''));

        if ($name !== '') {
            $query = Programme::query()->with('department.school');
            $normalizedName = $this->normalize($name);

            $programme = $query->get()->first(function (Programme $programme) use ($normalizedName, $department, $school): bool {
                if ($this->normalize($programme->name) !== $normalizedName) {
                    return false;
                }

                if ($department !== '' && $this->normalize($programme->department?->name ?? '') !== $department) {
                    return false;
                }

                if ($school !== '' && $this->normalize($programme->department?->school?->name ?? '') !== $school) {
                    return false;
                }

                return true;
            });

            if ($programme) {
                return $programme;
            }
        }

        $suggestions = $this->programmeSuggestions($code ?: $name);
        $suffix = $suggestions === '' ? '' : " Closest programmes: {$suggestions}.";

        throw new \InvalidArgumentException("Programme was not found. Use programme_code or exact programme name with optional school/department columns.{$suffix}");
    }

    private function normalize(string $value): string
    {
        return Str::of($value)->lower()->replaceMatches('/[^a-z0-9]+/', '')->toString();
    }

    private function programmeSuggestions(string $needle): string
    {
        $needle = $this->normalize($needle);

        return Programme::query()
            ->with('department.school')
            ->orderBy('name')
            ->get()
            ->map(fn (Programme $programme): array => [
                'label' => "{$programme->name} ({$programme->code})",
                'distance' => levenshtein($needle, $this->normalize($programme->name.' '.$programme->code)),
            ])
            ->sortBy('distance')
            ->take(3)
            ->pluck('label')
            ->join(', ');
    }

    public function scholarshipProgrammeSuggestions(string $needle): string
    {
        $needle = $this->normalize($needle);

        return ScholarshipProgramme::query()
            ->orderBy('name')
            ->get()
            ->map(fn (ScholarshipProgramme $programme): array => [
                'label' => "{$programme->name} ({$programme->code})",
                'distance' => levenshtein($needle, $this->normalize($programme->name.' '.$programme->code)),
            ])
            ->sortBy('distance')
            ->take(3)
            ->pluck('label')
            ->join(', ');
    }
}
