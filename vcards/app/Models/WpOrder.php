<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WpOrder extends Model
{
    use HasFactory;

    protected $table = 'wp_orders';

    protected $fillable = [
        'wp_store_id',
        'order_id',
        'name',
        'phone',
        'region_code',
        'address',
        'discount',
        'discount_amount',
        'grand_total',
        'status',
        'order_type',
        'delivery_charge',
    ];

    const TAKE_AWAY = 1;
    const DELIVERY = 2;

    const ORDER_TYPE_ARR = [
        self::TAKE_AWAY => 'Take Away',
        self::DELIVERY => 'Delivery',
    ];
    

    const PENDING = 0;
    const DISPATCHED = 1;
    const DELIVERED = 2;
    const CANCELLED = 3;

    const STATUS_ARR = [
        self::PENDING => 'Pending',
        self::DISPATCHED => 'Dispatched',
        self::DELIVERED => 'Delivered',
        self::CANCELLED => 'Cancelled',
    ];

    public static $rules = [
        'wp_store_id' => 'required|exists:whatsapp_stores,id',
        'name' => 'required',
        'phone' => 'required|numeric',
        'region_code' => 'required',
        'address' => 'required',
        'discount' => 'nullable',
        'discount_amount' => 'nullable',
        'grand_total' => 'required',
        'order_type' => 'required',
        'delivery_charge' => 'nullable',
    ];

    public function wpStore()
    {
        return $this->belongsTo(WhatsappStore::class, 'wp_store_id');
    }

    public function products()
    {
        return $this->hasMany(WpOrderItem::class);
    }
}
