<?php

namespace App\Models;

use App\Models\Traits\StorageLimit;
use Barryvdh\LaravelIdeHelper\Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * App\Models\Gallery
 *
 * @property int $id
 * @property string $type
 * @property string|null $link
 * @property int|null $category_id
 * @property int $vcard_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read string|null $category_name
 * @property-read string $gallery_image
 * @property-read string $type_name
 * @property-read GalleryCategory|null $category
 * @property-read GalleryCategory|null $galleryCategory
 * @property-read MediaCollection|Media[] $media
 * @property-read int|null $media_count
 * @property-read Vcard $vcard
 *
 * @method static Builder|Gallery newModelQuery()
 * @method static Builder|Gallery newQuery()
 * @method static Builder|Gallery query()
 * @method static Builder|Gallery whereCreatedAt($value)
 * @method static Builder|Gallery whereId($value)
 * @method static Builder|Gallery whereLink($value)
 * @method static Builder|Gallery whereType($value)
 * @method static Builder|Gallery whereUpdatedAt($value)
 * @method static Builder|Gallery whereVcardId($value)
 *
 * @mixin Eloquent
 */
class Gallery extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia, StorageLimit;

    const GALLERY_PATH = 'vcards/gallery';

    protected $table = 'galleries';

    protected $appends = ['gallery_image', 'type_name', 'category_name'];

    protected $with = ['media', 'category'];

    protected $fillable = [
        'type',
        'link',
        'category_id',
        'vcard_id',
    ];

    protected $casts = [
        'type' => 'string',
        'link' => 'string',
        'category_id' => 'integer',
        'vcard_id' => 'integer',
    ];

    const TYPE_IMAGE = 0;

    const TYPE_YOUTUBE = 1;

    const TYPE_FILE = 2;

    const TYPE_VIDEO = 3;

    const TYPE_AUDIO = 4;

    const TYPE = [
        self::TYPE_IMAGE => 'Image',
        self::TYPE_YOUTUBE => 'YouTube',
        self::TYPE_FILE => 'File',
        self::TYPE_VIDEO => 'Video',
        self::TYPE_AUDIO => 'Audio',
    ];

    public static $rules = [
        'type' => 'required',
        'link' => 'nullable|url',
        'category_id' => 'nullable|exists:gallery_categories,id',
    ];

    public function getGalleryImageAttribute(): string
    {
        /** @var Media $media */
        $media = $this->getMedia(self::GALLERY_PATH)->first();
        if ($media !== null) {
            return $media->getFullUrl();
        }

        return asset('assets/images/default_service.png');
    }

    public function vcard(): BelongsTo
    {
        return $this->belongsTo(Vcard::class, 'vcard_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(GalleryCategory::class, 'category_id');
    }

    public function getCategoryNameAttribute(): ?string
    {
        return $this->category?->name;
    }

    public function getTypeNameAttribute($value): string
    {
        return self::TYPE[$this->type];
    }
}
