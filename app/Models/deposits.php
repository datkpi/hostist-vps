<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class deposits extends Model
{

    use HasFactory;

    protected $fillable = [
        'transaction_code',
        'customer_id',
        'amount',
        'payment_method',
        'note',
        'status', // pending, approved, rejected
        'payment_details',
        'verified_by',
        'verified_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_details' => 'array',
        'verified_at' => 'datetime',
    ];

    // Relationship với Customer
    public function customer()
    {
        return $this->belongsTo(Customers::class, 'customer_id');
    }

    // Relationship với User (admin xác nhận)
    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    // Scope để lọc theo trạng thái
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }
}
