<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;

class Item extends Model
{
    use HasFactory;

    // Allow mass assignment
    protected $fillable = ['name', 'category_id', 'quantity'];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('item')
            ->logFillable()
            ->setDescriptionForEvent(fn(string $eventName) => "Item has been {$eventName}");
    }
}
