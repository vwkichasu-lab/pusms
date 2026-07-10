<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('student_scholarships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('scholarship_programme_id')->constrained()->restrictOnDelete();
            $table->foreignId('academic_year_id')->constrained()->restrictOnDelete();
            $table->foreignId('semester_id')->nullable()->constrained()->nullOnDelete();
            $table->date('award_date')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->decimal('coverage_percentage', 5, 2)->nullable();
            $table->boolean('covers_accommodation')->default(false);
            $table->boolean('covers_tuition')->default(true);
            $table->boolean('covers_stipend')->default(false);
            $table->text('coverage_notes')->nullable();
            $table->decimal('amount_awarded', 12, 2)->nullable();
            $table->string('status')->default('pending')->index();
            $table->string('award_reference')->nullable()->index();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->unique(
                ['student_id', 'scholarship_programme_id', 'academic_year_id', 'semester_id'],
                'student_scholarship_period_unique',
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_scholarships');
    }
};
