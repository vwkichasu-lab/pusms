<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_scholarships', function (Blueprint $table): void {
            if (! Schema::hasColumn('student_scholarships', 'scholarship_stage')) {
                $table->string('scholarship_stage')
                    ->default('existing_beneficiary')
                    ->index()
                    ->after('status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('student_scholarships', function (Blueprint $table): void {
            if (Schema::hasColumn('student_scholarships', 'scholarship_stage')) {
                $table->dropColumn('scholarship_stage');
            }
        });
    }
};
