<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliveryNumberSequence extends Model
{
    protected $fillable = [
        'sequence_date',
        'last_number',
    ];

    protected $casts = [
        'sequence_date' => 'date',
        'last_number' => 'integer',
    ];
}
