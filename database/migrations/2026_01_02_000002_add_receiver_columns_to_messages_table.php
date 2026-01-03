<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            if (!Schema::hasColumn('messages', 'ReceiverID')) {
                $table->unsignedBigInteger('ReceiverID')->nullable()->after('SenderType');
            }
            if (!Schema::hasColumn('messages', 'ReceiverType')) {
                $table->string('ReceiverType', 20)->nullable()->after('ReceiverID');
            }
            if (!Schema::hasColumn('messages', 'Subject')) {
                $table->string('Subject', 255)->nullable()->after('ReceiverType');
            }
        });
    }

    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            if (Schema::hasColumn('messages', 'Subject')) {
                $table->dropColumn('Subject');
            }
            if (Schema::hasColumn('messages', 'ReceiverType')) {
                $table->dropColumn('ReceiverType');
            }
            if (Schema::hasColumn('messages', 'ReceiverID')) {
                $table->dropColumn('ReceiverID');
            }
        });
    }
};
