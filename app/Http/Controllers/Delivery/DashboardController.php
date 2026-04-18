<?php

namespace App\Http\Controllers\Delivery;

use App\Http\Controllers\Controller;
use App\Models\Delivery;

class DashboardController extends Controller
{
    public function index()
    {
        $staffId = auth()->id();
        $today   = today();

        $todayBase = Delivery::where('delivery_staff_id', $staffId)
            ->where(fn ($q) => $q
                ->whereDate('assigned_at', $today)
                ->orWhereHas('order', fn ($oq) => $oq->whereDate('order_date', $today))
            );

        $todayTotal     = (clone $todayBase)->count();
        $todayDelivered = (clone $todayBase)->where('delivery_status', 'delivered')->count();
        $todayActive    = (clone $todayBase)->whereIn('delivery_status', ['assigned', 'in_progress', 'pending'])->count();
        $todayFailed    = (clone $todayBase)->where('delivery_status', 'failed')->count();

        $monthTotal = Delivery::where('delivery_staff_id', $staffId)
            ->whereMonth('assigned_at', now()->month)
            ->whereYear('assigned_at', now()->year)
            ->count();

        return view('delivery.dashboard', compact(
            'todayTotal', 'todayDelivered', 'todayActive', 'todayFailed', 'monthTotal'
        ));
    }
}
