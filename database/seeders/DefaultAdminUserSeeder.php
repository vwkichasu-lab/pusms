<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DefaultAdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $email = env('PUSMS_ADMIN_EMAIL');
        $password = env('PUSMS_ADMIN_PASSWORD');

        if (blank($email) || blank($password)) {
            $this->command?->warn('Skipped Super Administrator user. Set PUSMS_ADMIN_EMAIL and PUSMS_ADMIN_PASSWORD before running this seeder.');

            return;
        }

        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'name' => env('PUSMS_ADMIN_NAME', 'Super Administrator'),
                'password' => Hash::make($password),
                'is_active' => true,
            ],
        );

        $user->assignRole('Super Administrator');
    }
}
