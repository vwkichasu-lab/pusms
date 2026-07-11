<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('gmail_accounts', function (Blueprint $table): void {
            if (! Schema::hasColumn('gmail_accounts', 'connected_at')) {
                $table->timestamp('connected_at')->nullable()->after('scopes');
            }

            if (! Schema::hasColumn('gmail_accounts', 'revoked_at')) {
                $table->timestamp('revoked_at')->nullable()->after('connected_at');
            }

            if (! Schema::hasColumn('gmail_accounts', 'status')) {
                $table->string('status')->default('connected')->after('revoked_at')->index();
            }
        });
    }

    public function down(): void
    {
        Schema::table('gmail_accounts', function (Blueprint $table): void {
            foreach (['status', 'revoked_at', 'connected_at'] as $column) {
                if (Schema::hasColumn('gmail_accounts', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
