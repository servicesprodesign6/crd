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
            $table->boolean('can_create_whatsapp_store')->default(false)->after('can_edit_vcard');
            $table->boolean('can_edit_whatsapp_store')->default(false)->after('can_create_whatsapp_store');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('organisation_user_permissions', function (Blueprint $table) {
            $table->dropColumn(['can_create_whatsapp_store', 'can_edit_whatsapp_store']);
        });
    }
};
