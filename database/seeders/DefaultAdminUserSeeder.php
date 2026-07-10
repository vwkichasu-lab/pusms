<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DefaultAdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $this->createUserFromEnvironment(
            emailKey: 'PUSMS_ADMIN_EMAIL',
            passwordKey: 'PUSMS_ADMIN_PASSWORD',
            nameKey: 'PUSMS_ADMIN_NAME',
            defaultName: 'Super Administrator',
            role: 'Super Administrator',
        );

        $this->createUserFromEnvironment(
            emailKey: 'PUSMS_BETTY_EMAIL',
            passwordKey: 'PUSMS_BETTY_PASSWORD',
            nameKey: 'PUSMS_BETTY_NAME',
            defaultName: 'Betty',
            role: 'Super Administrator',
        );
    }

    private function createUserFromEnvironment(
        string $emailKey,
        string $passwordKey,
        string $nameKey,
        string $defaultName,
        string $role,
    ): void {
        $email = env($emailKey);
        $password = env($passwordKey);

        if (blank($email) || blank($password)) {
            $this->command?->warn("Skipped {$defaultName} user. Set {$emailKey} and {$passwordKey} before running this seeder.");

            return;
        }

        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'name' => env($nameKey, $defaultName),
                'password' => Hash::make($password),
                'is_active' => true,
            ],
        );

        $user->syncRoles([$role]);
    }
}
