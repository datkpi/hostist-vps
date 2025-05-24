<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Categories extends Model
{
    use HasFactory;
     protected $fillable = [
        'name',
        'slug',
        'description',
        'parent_id',
        'image',
        'status', // active, inactive
        'sort_order',
    ];

    // Relationship với Category cha
    public function parent()
    {
        return $this->belongsTo(Categories::class, 'parent_id');
    }

    // Relationship với các Category con
    public function children()
    {
        return $this->hasMany(Categories::class, 'parent_id');
    }

    // Relationship với Product
    public function products()
    {
        return $this->hasMany(Products::class, 'category_id');
    }
}
