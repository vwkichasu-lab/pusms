<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $seeders = [
            RolePermissionSeeder::class,
            AcademicStructureSeeder::class,
            GhanaAdministrativeSeeder::class,
            ChurchAreaDistrictSeeder::class,
            DefaultAdminUserSeeder::class,
        ];

        if (filter_var(env('PUSMS_SEED_SAMPLE_DATA', false), FILTER_VALIDATE_BOOLEAN)) {
            $seeders[] = SampleScholarshipDataSeeder::class;
        }

        $this->call($seeders);
    }
}
