<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class BusinessAsset extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'asset_name', 'category', 'purchase_date', 'purchase_cost', 'supplier_id',
        'supplier_name', 'warranty_date', 'current_status', 'location', 'note',
        'attachment', 'created_by', 'updated_by',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'purchase_cost' => 'decimal:2',
        'warranty_date' => 'date',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function maintenanceCosts(): HasMany
    {
        return $this->hasMany(MaintenanceCost::class);
    }

    public static function categoryLabels(): array
    {
        return [
            'Water Purifier' => 'Water Purifier',
            'Filter Machine' => 'Filter Machine',
            'Pump' => 'Pump',
            'Delivery Van/Bike' => 'Delivery Van/Bike',
            'Storage Tank' => 'Storage Tank',
            'Washing Machine' => 'Washing Machine',
            'Sealing Machine' => 'Sealing Machine',
            'Office Equipment' => 'Office Equipment',
            'Maintenance Tools' => 'Maintenance Tools',
        ];
    }

    public static function statusLabels(): array
    {
        return ['active' => 'Active', 'damaged' => 'Damaged', 'sold' => 'Sold', 'repaired' => 'Repaired'];
    }
}
