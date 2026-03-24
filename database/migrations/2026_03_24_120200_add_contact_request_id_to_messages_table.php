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
        if (!Schema::hasColumn('messages', 'contact_request_id')) {
            Schema::table('messages', function (Blueprint $table) {
                $table->unsignedBigInteger('contact_request_id')->nullable()->after('receiver_id');
                $table->index('contact_request_id', 'messages_contact_request_id_index');
            });
        }

        Schema::table('messages', function (Blueprint $table) {
            $table->foreign('contact_request_id', 'messages_contact_request_id_foreign')
                ->references('id')
                ->on('contact_requests')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropForeign('messages_contact_request_id_foreign');
            $table->dropIndex('messages_contact_request_id_index');
            $table->dropColumn('contact_request_id');
        });
    }
};
