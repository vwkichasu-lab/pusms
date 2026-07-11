<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DefaultAdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $primaryAdmin = $this->createUserFromEnvironment(
            emailKey: 'PUSMS_ADMIN_EMAIL',
            passwordKey: 'PUSMS_ADMIN_PASSWORD',
            nameKey: 'PUSMS_ADMIN_NAME',
            usernameKey: 'PUSMS_ADMIN_USERNAME',
            defaultName: 'Super Administrator',
            defaultUsername: 'Admin',
            role: 'Super Administrator',
        );

        $this->createUserFromEnvironment(
            emailKey: 'PUSMS_BETTY_EMAIL',
            passwordKey: 'PUSMS_BETTY_PASSWORD',
            nameKey: 'PUSMS_BETTY_NAME',
            usernameKey: 'PUSMS_BETTY_USERNAME',
            defaultName: 'Betty',
            defaultUsername: 'Betty',
            role: 'Scholarship Secretary',
        );

        if ($primaryAdmin) {
            User::query()
                ->whereKeyNot($primaryAdmin->id)
                ->get()
                ->filter(fn (User $user): bool => $user->hasRole('Super Administrator'))
                ->each(fn (User $user): mixed => $user->syncRoles(['Scholarship Secretary']));
        }
    }

    private function createUserFromEnvironment(
        string $emailKey,
        string $passwordKey,
        string $nameKey,
        string $usernameKey,
        string $defaultName,
        string $defaultUsername,
        string $role,
    ): ?User {
        $email = env($emailKey);
        $password = env($passwordKey);

        if (blank($email) || blank($password)) {
            $this->command?->warn("Skipped {$defaultName} user. Set {$emailKey} and {$passwordKey} before running this seeder.");

            return null;
        }

        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'name' => env($nameKey, $defaultName),
                'username' => env($usernameKey, $defaultUsername),
                'password' => Hash::make($password),
                'is_active' => true,
            ],
        );

        $user->syncRoles([$role]);

        return $user;
    }
}
