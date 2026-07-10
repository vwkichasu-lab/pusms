<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('communications', function (Blueprint $table): void {
            if (! Schema::hasColumn('communications', 'attachment_path')) {
                $table->string('attachment_path')->nullable()->after('message');
                $table->string('attachment_original_name')->nullable()->after('attachment_path');
            }
        });
    }

    public function down(): void
    {
        Schema::table('communications', function (Blueprint $table): void {
            foreach (['attachment_path', 'attachment_original_name'] as $column) {
                if (Schema::hasColumn('communications', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
