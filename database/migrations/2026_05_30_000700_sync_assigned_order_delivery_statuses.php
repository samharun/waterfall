<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('deliveries')
            ->where('delivery_status', 'pending')
            ->whereNull('deleted_at')
            ->whereExists(function ($query) {
                $query->selectRaw('1')
                    ->from('orders')
                    ->whereColumn('orders.id', 'deliveries.order_id')
                    ->where('orders.order_status', 'assigned')
                    ->whereNull('orders.deleted_at');
            })
            ->update([
                'delivery_status' => 'assigned',
                'assigned_at' => DB::raw('COALESCE(assigned_at, updated_at, created_at)'),
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        //
    }
};
