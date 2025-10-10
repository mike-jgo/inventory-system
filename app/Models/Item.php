<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    use HasFactory;

    // Allow mass assignment
    protected $fillable = ['name', 'category_id', 'quantity'];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
