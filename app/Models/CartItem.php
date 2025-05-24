<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'cart_id',
        'product_id',
        'name',
        'quantity',
        'unit_price',
        'tax_percent',
        'tax_amount',
        'discount_percent',
        'discount_amount',
        'subtotal',
        'total',
        'options',
    ];

    protected $casts = [
        'options' => 'array',
    ];

    // Relationship với Cart
    public function cart()
    {
        return $this->belongsTo(Cart::class);
    }

    // Relationship với Product
    public function product()
    {
        return $this->belongsTo(Products::class);
    }

    // Lấy thời hạn từ options
    public function getPeriodAttribute()
    {
        $options = json_decode($this->options, true) ?: [];
        return $options['period'] ?? 1;
    }

    // Lấy tự động gia hạn từ options
    public function getAutoRenewAttribute()
    {
        $options = json_decode($this->options, true) ?: [];
        return $options['auto_renew'] ?? false;
    }

    // Format số tiền
    public function getFormattedUnitPriceAttribute()
    {
        return number_format($this->unit_price, 0, ',', '.') . ' đ';
    }

    public function getFormattedSubtotalAttribute()
    {
        return number_format($this->subtotal, 0, ',', '.') . ' đ';
    }
}
