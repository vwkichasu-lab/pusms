<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sponsors', function (Blueprint $table): void {
            $table->string('sponsor_type')->nullable()->after('name')->index();
            $table->text('notes')->nullable()->after('address');
        });

        Schema::table('scholarship_programmes', function (Blueprint $table): void {
            $table->foreignId('academic_year_id')->nullable()->after('sponsor_id')->constrained()->nullOnDelete();
            $table->decimal('default_coverage_percentage', 5, 2)->nullable()->after('coverage_type');
            $table->boolean('default_covers_accommodation')->default(false)->after('default_coverage_percentage');
            $table->boolean('is_renewable')->default(true)->after('default_covers_accommodation');
            $table->text('eligibility_criteria')->nullable()->after('description');
        });

        Schema::table('student_scholarships', function (Blueprint $table): void {
            $table->unique('award_reference', 'student_scholarships_award_reference_unique');
        });
    }

    public function down(): void
    {
        Schema::table('student_scholarships', function (Blueprint $table): void {
            $table->dropUnique('student_scholarships_award_reference_unique');
        });

        Schema::table('scholarship_programmes', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('academic_year_id');
            $table->dropColumn([
                'default_coverage_percentage',
                'default_covers_accommodation',
                'is_renewable',
                'eligibility_criteria',
            ]);
        });

        Schema::table('sponsors', function (Blueprint $table): void {
            $table->dropColumn(['sponsor_type', 'notes']);
        });
    }
};
