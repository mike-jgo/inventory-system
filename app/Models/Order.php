<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Order extends Model
{
    use LogsActivity;

    protected $fillable = [
        'user_id',
        'customer_name',
        'type',
        'status',
        'total_amount',
        'payment_method',
        'amount_paid',
        'change_due',
        'payment_reference',
        'wastage',
    ];


    protected $casts = [
        'wastage' => 'boolean',
        'total_amount' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'change_due' => 'decimal:2',
    ];


    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function items()
    {
        return $this->belongsToMany(Item::class, 'order_items')
            ->withPivot('quantity', 'price')
            ->withTimestamps();
    }
}
