<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('students', 'country')) {
            Schema::table('students', function (Blueprint $table): void {
                $table->string('country')->default('Ghana')->after('home_town')->index();
            });
        }

        Schema::table('communication_recipients', function (Blueprint $table): void {
            if (! Schema::hasColumn('communication_recipients', 'sponsor_id')) {
                $table->foreignId('sponsor_id')->nullable()->after('student_id')->constrained()->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('communication_recipients', function (Blueprint $table): void {
            if (Schema::hasColumn('communication_recipients', 'sponsor_id')) {
                $table->dropConstrainedForeignId('sponsor_id');
            }
        });

        if (Schema::hasColumn('students', 'country')) {
            Schema::table('students', function (Blueprint $table): void {
                $table->dropColumn('country');
            });
        }
    }
};
