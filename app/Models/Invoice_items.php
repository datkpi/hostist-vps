<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice_items extends Model
{
    use HasFactory;
    protected $fillable = [
        'invoice_id',
        'order_item_id',
        'product_id',
        'name',
        'description',
        'quantity',
        'unit_price',
        'tax_percent',
        'tax_amount',
        'discount_percent',
        'discount_amount',
        'subtotal',
        'total',
    ];

    // Relationship với Invoice
    public function invoice()
    {
        return $this->belongsTo(Invoices::class);
    }

    // Relationship với OrderItem
    public function orderItem()
    {
        return $this->belongsTo(Order_items::class);
    }

    // Relationship với Product
    public function product()
    {
        return $this->belongsTo(Products::class);
    }
}
