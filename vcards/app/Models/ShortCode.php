<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShortCode extends Model
{
    use HasFactory;

    protected $table = 'short_codes';

    protected $fillable = [
        'email_template_type',
        'short_code',
        'value',
    ];
}
