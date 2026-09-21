<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrganisationUserPermission extends Model
{
    protected $fillable = [
        'user_id',
        'no_of_vcards',
        'can_create_vcard',
        'can_edit_vcard',
        'no_of_whatsapp_store',
        'can_create_whatsapp_store',
        'can_edit_whatsapp_store',
    ];

    protected $casts = [
        'no_of_vcards' => 'integer',
        'can_create_vcard' => 'boolean',
        'can_edit_vcard' => 'boolean',
        'no_of_whatsapp_store' => 'integer',
        'can_create_whatsapp_store' => 'boolean',
        'can_edit_whatsapp_store' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
