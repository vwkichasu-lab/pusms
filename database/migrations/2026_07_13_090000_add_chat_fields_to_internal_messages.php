<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('internal_messages', function (Blueprint $table): void {
            if (! Schema::hasColumn('internal_messages', 'broadcast_group_id')) {
                $table->string('broadcast_group_id')->nullable()->index()->after('recipient_id');
            }

            if (! Schema::hasColumn('internal_messages', 'deleted_by_sender_at')) {
                $table->timestamp('deleted_by_sender_at')->nullable()->index()->after('read_at');
            }

            if (! Schema::hasColumn('internal_messages', 'deleted_by_recipient_at')) {
                $table->timestamp('deleted_by_recipient_at')->nullable()->index()->after('deleted_by_sender_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('internal_messages', function (Blueprint $table): void {
            foreach (['deleted_by_recipient_at', 'deleted_by_sender_at', 'broadcast_group_id'] as $column) {
                if (Schema::hasColumn('internal_messages', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
