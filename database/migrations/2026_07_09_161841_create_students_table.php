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
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->string('student_id')->unique();
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name');
            $table->string('email')->unique();
            $table->string('phone');
            $table->foreignId('programme_id')->constrained()->restrictOnDelete();
            $table->foreignId('level_id')->constrained()->restrictOnDelete();
            $table->year('admission_year');
            $table->string('student_status')->default('active')->index();
            $table->string('student_batch')->nullable()->index();
            $table->year('graduation_year')->nullable()->index();
            $table->string('alumni_status')->default('not_alumni')->index();
            $table->string('alumni_badge')->nullable()->index();
            $table->date('date_of_birth')->nullable();
            $table->string('home_town')->nullable();
            $table->string('country')->default('Ghana')->index();
            $table->string('district')->nullable()->index();
            $table->string('region')->nullable()->index();
            $table->string('profile_photo')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['programme_id', 'level_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
