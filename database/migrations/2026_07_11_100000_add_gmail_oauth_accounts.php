<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gmail_accounts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('google_user_id')->nullable()->index();
            $table->string('email')->index();
            $table->string('name')->nullable();
            $table->text('access_token');
            $table->text('refresh_token')->nullable();
            $table->timestamp('token_expires_at')->nullable();
            $table->json('scopes')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'email']);
        });

        Schema::table('communications', function (Blueprint $table): void {
            $table->foreignId('gmail_account_id')->nullable()->after('created_by')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('communications', function (Blueprint $table): void {
            if (Schema::hasColumn('communications', 'gmail_account_id')) {
                $table->dropConstrainedForeignId('gmail_account_id');
            }
        });

        Schema::dropIfExists('gmail_accounts');
    }
};
