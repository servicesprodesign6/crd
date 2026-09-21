<?php

namespace App\Models;

use Barryvdh\LaravelIdeHelper\Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\GalleryCategory
 *
 * @property int $id
 * @property int $vcard_id
 * @property string $name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Vcard $vcard
 *
 * @method static Builder|GalleryCategory newModelQuery()
 * @method static Builder|GalleryCategory newQuery()
 * @method static Builder|GalleryCategory query()
 * @method static Builder|GalleryCategory whereCreatedAt($value)
 * @method static Builder|GalleryCategory whereId($value)
 * @method static Builder|GalleryCategory whereName($value)
 * @method static Builder|GalleryCategory whereUpdatedAt($value)
 * @method static Builder|GalleryCategory whereVcardId($value)
 *
 * @mixin Eloquent
 */
class GalleryCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'vcard_id',
        'name',
    ];

    protected $casts = [
        'vcard_id' => 'integer',
        'name' => 'string',
    ];

    public static $rules = [
        'vcard_id' => 'required|exists:vcards,id',
    ];

    public function vcard(): BelongsTo
    {
        return $this->belongsTo(Vcard::class, 'vcard_id');
    }

    public function products()
    {
        return $this->hasMany(Gallery::class, 'category_id', 'id');
    }
}
