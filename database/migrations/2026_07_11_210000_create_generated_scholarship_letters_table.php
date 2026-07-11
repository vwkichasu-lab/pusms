<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('generated_scholarship_letters', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('student_scholarship_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('generated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('reference')->nullable();
            $table->date('letter_date')->nullable();
            $table->string('signatory_name')->nullable();
            $table->string('signatory_title')->nullable();
            $table->text('body')->nullable();
            $table->timestamp('generated_at')->index();
            $table->timestamps();

            $table->index(['student_id', 'generated_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('generated_scholarship_letters');
    }
};
