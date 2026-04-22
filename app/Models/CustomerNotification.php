<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerNotification extends Model
{
    protected $fillable = [
        'customer_id',
        'order_id',
        'type',
        'title',
        'body',
        'data_json',
        'read_at',
    ];

    protected $casts = [
        'data_json' => 'array',
        'read_at' => 'datetime',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
