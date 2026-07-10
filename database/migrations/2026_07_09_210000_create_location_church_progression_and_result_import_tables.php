<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ghana_regions', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->unique();
            $table->string('capital')->nullable();
            $table->timestamps();
        });

        Schema::create('ghana_districts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('ghana_region_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('category')->nullable()->index();
            $table->string('capital')->nullable();
            $table->timestamps();

            $table->unique(['ghana_region_id', 'name']);
        });

        Schema::create('church_areas', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->unique();
            $table->string('status')->default('active')->index();
            $table->timestamps();
        });

        Schema::create('church_districts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('church_area_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('status')->default('active')->index();
            $table->timestamps();

            $table->unique(['church_area_id', 'name']);
        });

        if (Schema::hasTable('communication_recipients')) {
            $driver = Schema::getConnection()->getDriverName();

            if ($driver !== 'sqlite') {
                Schema::table('communication_recipients', function (Blueprint $table): void {
                    $table->foreignId('student_id')->nullable()->change();
                });
            } elseif ($this->sqliteColumnIsNotNullable('communication_recipients', 'student_id')) {
                Schema::disableForeignKeyConstraints();
                Schema::rename('communication_recipients', 'communication_recipients_old');

                Schema::create('communication_recipients', function (Blueprint $table): void {
                    $table->id();
                    $table->foreignId('communication_id')->constrained()->cascadeOnDelete();
                    $table->foreignId('student_id')->nullable()->constrained()->cascadeOnDelete();
                    $table->foreignId('sponsor_id')->nullable()->constrained()->nullOnDelete();
                    $table->string('channel')->index();
                    $table->string('destination');
                    $table->string('delivery_status')->default('pending')->index();
                    $table->string('provider_message_id')->nullable();
                    $table->timestamp('sent_at')->nullable();
                    $table->timestamp('delivered_at')->nullable();
                    $table->timestamp('failed_at')->nullable();
                    $table->text('failure_reason')->nullable();
                    $table->json('provider_response')->nullable();
                    $table->timestamps();
                    $table->index(['communication_id', 'delivery_status']);
                });

                DB::statement(<<<'SQL'
                    INSERT INTO communication_recipients (
                        id, communication_id, student_id, sponsor_id, channel, destination, delivery_status,
                        provider_message_id, sent_at, delivered_at, failed_at, failure_reason,
                        provider_response, created_at, updated_at
                    )
                    SELECT
                        id, communication_id, student_id, sponsor_id, channel, destination, delivery_status,
                        provider_message_id, sent_at, delivered_at, failed_at, failure_reason,
                        provider_response, created_at, updated_at
                    FROM communication_recipients_old
                SQL);

                Schema::drop('communication_recipients_old');
                Schema::enableForeignKeyConstraints();
            }
        }

        Schema::table('scholarship_programmes', function (Blueprint $table): void {
            if (! Schema::hasColumn('scholarship_programmes', 'scholarship_type')) {
                $table->string('scholarship_type')->default('pu_bursary')->after('coverage_type')->index();
                $table->boolean('requires_church_area')->default(false)->after('scholarship_type');
                $table->boolean('requires_church_district')->default(false)->after('requires_church_area');
                $table->foreignId('church_area_id')->nullable()->after('requires_church_district')->constrained()->nullOnDelete();
                $table->foreignId('church_district_id')->nullable()->after('church_area_id')->constrained()->nullOnDelete();
            }
        });

        Schema::table('student_results', function (Blueprint $table): void {
            if (! Schema::hasColumn('student_results', 'course_code')) {
                $table->string('index_number')->nullable()->after('student_id')->index();
                $table->string('programme_snapshot')->nullable()->after('semester_id');
                $table->string('level_snapshot')->nullable()->after('programme_snapshot');
                $table->string('course_code')->nullable()->after('level_snapshot')->index();
                $table->string('course_name')->nullable()->after('course_code');
                $table->unsignedSmallInteger('credit_hours')->nullable()->after('course_name');
                $table->string('grade')->nullable()->after('credit_hours');
                $table->decimal('grade_point', 4, 2)->nullable()->after('grade');
                $table->decimal('score', 5, 2)->nullable()->after('grade_point');
                $table->string('data_source')->default('Manual Entry')->after('performance_status')->index();
                $table->timestamp('created_or_imported_at')->nullable()->after('data_source');
            }
        });

        Schema::create('result_imports', function (Blueprint $table): void {
            $table->id();
            $table->string('original_filename');
            $table->string('stored_path');
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status')->default('uploaded')->index();
            $table->unsignedInteger('total_rows')->default(0);
            $table->unsignedInteger('valid_rows')->default(0);
            $table->unsignedInteger('invalid_rows')->default(0);
            $table->json('preview_rows')->nullable();
            $table->json('errors')->nullable();
            $table->timestamps();
        });

        Schema::create('student_level_progressions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('previous_level_id')->nullable()->constrained('levels')->nullOnDelete();
            $table->foreignId('new_level_id')->nullable()->constrained('levels')->nullOnDelete();
            $table->foreignId('academic_year_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('update_type')->default('individual')->index();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_level_progressions');
        Schema::dropIfExists('result_imports');

        Schema::table('student_results', function (Blueprint $table): void {
            foreach (['index_number', 'programme_snapshot', 'level_snapshot', 'course_code', 'course_name', 'credit_hours', 'grade', 'grade_point', 'score', 'data_source', 'created_or_imported_at'] as $column) {
                if (Schema::hasColumn('student_results', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('scholarship_programmes', function (Blueprint $table): void {
            if (Schema::hasColumn('scholarship_programmes', 'church_district_id')) {
                $table->dropConstrainedForeignId('church_district_id');
            }

            if (Schema::hasColumn('scholarship_programmes', 'church_area_id')) {
                $table->dropConstrainedForeignId('church_area_id');
            }

            foreach (['requires_church_district', 'requires_church_area', 'scholarship_type'] as $column) {
                if (Schema::hasColumn('scholarship_programmes', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::dropIfExists('church_districts');
        Schema::dropIfExists('church_areas');
        Schema::dropIfExists('ghana_districts');
        Schema::dropIfExists('ghana_regions');
    }

    private function sqliteColumnIsNotNullable(string $table, string $column): bool
    {
        foreach (DB::select("PRAGMA table_info({$table})") as $definition) {
            if ($definition->name === $column) {
                return (bool) $definition->notnull;
            }
        }

        return false;
    }
};
