<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('conversations', function (Blueprint $table) {
            if (!Schema::hasColumn('conversations', 'LastMessageID')) {
                $table->unsignedBigInteger('LastMessageID')->nullable()->after('LastMessageAt');
                
                // We typically want a foreign key, but we need to ensure the messages table exists and has the ID.
                // Given the circular nature (Message -> Conversation, Conversation -> LastMessage), 
                // we'll add the constraint if possible, or just the column.
                // Using 'set null' on delete prevents breaking the conversation if the message is deleted.
                $table->foreign('LastMessageID')->references('MessageID')->on('messages')->onDelete('set null');
            }
        });
    }

    public function down(): void
    {
        Schema::table('conversations', function (Blueprint $table) {
            if (Schema::hasColumn('conversations', 'LastMessageID')) {
                // Drop foreign key first if it exists. 
                // The name is typically conversations_lastmessageid_foreign
                $table->dropForeign(['LastMessageID']);
                $table->dropColumn('LastMessageID');
            }
        });
    }
};
