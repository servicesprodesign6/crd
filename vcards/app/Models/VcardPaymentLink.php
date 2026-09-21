<?php

namespace App\Models;

use App\Models\Traits\StorageLimit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class VcardPaymentLink extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia, StorageLimit;

    protected $table = 'vcard_payment_links';

    protected $with = ['media'];

    protected $appends = ['display_type_label'];

    /**
     * @var string[]
     */
    protected $fillable = [
        'vcard_id',
        'label',
        'display_type',
        'description',
    ];

    public static $rules = [
        'vcard_id' => 'required|exists:vcards,id',
        'label' => 'required|string|max:255',
        'display_type' => 'required|in:1,2,3,4',
        'description' => 'nullable|string|max:1000',
        'icon' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        'image' => 'nullable|image',
    ];

    const IMAGE_COLLECTION = 'vcards/payment_link_image';
    const DESCRIPTION_COLLECTION = 'vcards/payment_link_description';

    const DEFAULT = 1;
    const UPI = 2;
    const LINK = 3;
    const IMAGE = 4;

    const PAYMENT_LINKS = [
        self::DEFAULT => 'default',
        self::UPI => 'upi',
        self::LINK => 'link',
        self::IMAGE => 'image',
    ];

    public static function paymentLinks(): array
    {
        return [
            self::DEFAULT => __('messages.common.default'),
            self::UPI => __('messages.vcard.upi'),
            self::LINK => __('messages.common.link'),
            self::IMAGE => __('messages.vcard.image'),
        ];
    }


    public function vcard()
    {
        return $this->belongsTo(Vcard::class, 'vcard_id');
    }

    public function getDisplayTypeLabelAttribute()
    {
        return self::PAYMENT_LINKS[$this->display_type] ?? 'unknown';
    }

    public function scopeForVcard($query, $vcardId)
    {
        return $query->where('vcard_id', $vcardId);
    }
}
