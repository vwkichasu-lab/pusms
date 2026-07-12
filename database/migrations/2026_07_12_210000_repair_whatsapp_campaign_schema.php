<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('message_templates')) {
            Schema::table('message_templates', function (Blueprint $table): void {
                if (! Schema::hasColumn('message_templates', 'recipient_type')) {
                    $table->string('recipient_type')->default('all')->index()->after('channel');
                }

                if (! Schema::hasColumn('message_templates', 'is_active')) {
                    $table->boolean('is_active')->default(true)->index()->after('status');
                }
            });
        }

        if (! Schema::hasTable('message_campaigns')) {
            Schema::create('message_campaigns', function (Blueprint $table): void {
                $table->id();
                $table->string('campaign_name');
                $table->string('recipient_type')->index();
                $table->string('subject')->nullable();
                $table->text('message_body');
                $table->string('channel')->default('whatsapp')->index();
                $table->unsignedInteger('total_recipients')->default(0);
                $table->unsignedInteger('valid_recipients')->default(0);
                $table->unsignedInteger('invalid_recipients')->default(0);
                $table->unsignedInteger('pending_count')->default(0);
                $table->unsignedInteger('opened_count')->default(0);
                $table->unsignedInteger('sent_count')->default(0);
                $table->unsignedInteger('skipped_count')->default(0);
                $table->unsignedInteger('failed_count')->default(0);
                $table->string('status')->default('Ready')->index();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('completed_at')->nullable();
                $table->json('filters')->nullable();
                $table->timestamps();

                $table->index(['channel', 'recipient_type']);
                $table->index(['created_by', 'created_at']);
            });
        }

        if (! Schema::hasTable('message_campaign_recipients')) {
            Schema::create('message_campaign_recipients', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('campaign_id')->constrained('message_campaigns')->cascadeOnDelete();
                $table->string('recipient_type')->index();
                $table->unsignedBigInteger('recipient_id')->nullable();
                $table->string('recipient_name');
                $table->string('phone_number')->nullable();
                $table->string('normalized_phone')->nullable()->index();
                $table->text('personalized_message')->nullable();
                $table->text('whatsapp_url')->nullable();
                $table->string('status')->default('Pending')->index();
                $table->text('validation_error')->nullable();
                $table->timestamp('opened_at')->nullable();
                $table->timestamp('marked_sent_at')->nullable();
                $table->timestamp('skipped_at')->nullable();
                $table->timestamps();

                $table->unique(['campaign_id', 'recipient_type', 'recipient_id'], 'campaign_recipient_unique');
                $table->index(['campaign_id', 'status']);
            });
        }

        if (! Schema::hasTable('permissions')) {
            return;
        }

        $permissions = [
            'communication.view',
            'communication.create',
            'communication.send',
            'communication.manage_templates',
            'communication.view_history',
            'communication.delete_campaign',
        ];

        foreach ($permissions as $permission) {
            DB::table('permissions')->updateOrInsert(
                ['name' => $permission, 'guard_name' => 'web'],
                ['created_at' => now(), 'updated_at' => now()],
            );
        }

        if (! Schema::hasTable('roles') || ! Schema::hasTable('role_has_permissions')) {
            return;
        }

        $roleIds = DB::table('roles')
            ->whereIn('name', ['Super Administrator', 'Scholarship Secretary'])
            ->pluck('id');

        foreach ($roleIds as $roleId) {
            foreach ($permissions as $permission) {
                $permissionId = DB::table('permissions')->where('name', $permission)->value('id');

                if ($permissionId) {
                    DB::table('role_has_permissions')->updateOrInsert([
                        'permission_id' => $permissionId,
                        'role_id' => $roleId,
                    ]);
                }
            }
        }
    }
};
