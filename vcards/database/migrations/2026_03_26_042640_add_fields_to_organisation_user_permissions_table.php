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
        Schema::table('organisation_user_permissions', function (Blueprint $table) {
            $table->integer('no_of_vcards')->after('user_id')->nullable();
            $table->integer('no_of_whatsapp_store')->after('can_edit_vcard')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('organisation_user_permissions', function (Blueprint $table) {
            $table->dropColumn(['no_of_vcards', 'no_of_whatsapp_store']);
        });
    }
};
