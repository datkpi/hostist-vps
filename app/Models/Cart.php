<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'session_id',
        'subtotal',
        'tax_amount',
        'discount_amount',
        'total_amount',
        'coupon_code',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    // Relationship với User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relationship với CartItem
    public function items()
    {
        return $this->hasMany(CartItem::class);
    }

    // Lấy tổng số sản phẩm trong giỏ hàng
    public function getTotalItemsAttribute()
    {
        return $this->items->sum('quantity');
    }

    // Format số tiền
    public function getFormattedSubtotalAttribute()
    {
        return number_format($this->subtotal, 0, ',', '.') . ' đ';
    }

    public function getFormattedTotalAttribute()
    {
        return number_format($this->total_amount, 0, ',', '.') . ' đ';
    }
}
