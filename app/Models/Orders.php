<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Orders extends Model
{
    use HasFactory;
    protected $fillable = [
        'order_number',
        'customer_id',
        'status', // pending, processing, completed, cancelled
        'subtotal',
        'tax_amount',
        'discount_amount',
        'total_amount',
        'notes',
        'created_by', // user_id của người tạo
    ];

    // Relationship với Customer
    public function customer()
    {
        return $this->belongsTo(Customers::class);
    }

    // Relationship với User (người tạo)
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Relationship với OrderItem
    public function items()
    {
        return $this->hasMany(Order_items::class, 'order_id');
    }

    public function invoice()
    {
        return $this->hasOne(Invoices::class, 'order_id');
    }

    // Relationship với Payment
    public function payments()
    {
        return $this->hasMany(Payments::class, 'order_id');
    }
}
