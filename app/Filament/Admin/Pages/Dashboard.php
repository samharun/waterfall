<?php

namespace App\Filament\Admin\Pages;

use App\Models\{Customer, Dealer, Delivery, Order, Payment, User};
use Filament\Pages\Dashboard as BaseDashboard;
use Illuminate\Support\Carbon;

class Dashboard extends BaseDashboard
{
    protected static ?string $title = 'Operations Dashboard';
    protected string $view = 'filament.admin.pages.operations-dashboard';
    public string $selectedDate = '';
    public string $search = '';
    public int $chartDays = 7;
    public string $lastUpdatedAt = '';

    public function mount(): void
    {
        $this->selectedDate = today()->toDateString();
        $this->refreshDashboard();
    }

    public function refreshDashboard(): void
    {
        $this->lastUpdatedAt = now()->format('h:i A');
    }

    public function updatedSelectedDate(): void
    {
        $this->validate(['selectedDate' => ['required', 'date_format:Y-m-d']]);
        $this->refreshDashboard();
    }

    public function getDashboardData(): array
    {
        $this->validate(['selectedDate' => ['required', 'date_format:Y-m-d'], 'chartDays' => ['required', 'integer', 'in:7,14'], 'search' => ['string', 'max:100']]);
        $date = Carbon::parse($this->selectedDate);
        $user = auth()->user();
        $zones = $user?->role === 'delivery_manager'
            ? $user->managedZones()->pluck('id')->merge($user->zones()->pluck('zones.id'))->unique()->all() : null;
        $scope = fn ($q) => $q->when($zones !== null, fn ($q) => $q->whereIn('zone_id', $zones));
        $orders = $scope(Order::query());
        $customers = $scope(Customer::query());
        $dealers = $scope(Dealer::query());
        $payments = Payment::accepted()->when($zones !== null, fn ($q) => $q->where(fn ($q) => $q
            ->whereHas('customer', fn ($q) => $q->whereIn('zone_id', $zones))
            ->orWhereHas('dealer', fn ($q) => $q->whereIn('zone_id', $zones))));
        $dayDeliveries = $scope(Delivery::query())->where('delivery_status', '!=', 'cancelled')
            ->where(fn ($q) => $q->whereDate('assigned_at', $date)->orWhere(fn ($q) => $q->whereNull('assigned_at')->whereDate('created_at', $date)));
        $counts = (clone $dayDeliveries)->selectRaw('delivery_status, COUNT(*) as total')->groupBy('delivery_status')->pluck('total', 'delivery_status');
        $total = (int) $counts->sum();
        $delivered = (int) ($counts['delivered'] ?? 0);
        $pending = (int) ($counts['pending'] ?? 0);
        $inProgress = (int) ($counts['assigned'] ?? 0) + (int) ($counts['in_progress'] ?? 0);
        $failed = (int) ($counts['failed'] ?? 0) + (int) ($counts['not_delivered'] ?? 0) + (int) ($counts['customer_unavailable'] ?? 0);
        $partial = (int) ($counts['partial_delivered'] ?? 0);
        $start = $date->copy()->subDays($this->chartDays - 1);
        $daily = $scope(Delivery::query())->where('delivery_status', 'delivered')
            ->whereBetween('delivered_at', [$start->copy()->startOfDay(), $date->copy()->endOfDay()])
            ->selectRaw('DATE(delivered_at) as day, COUNT(*) as total')->groupByRaw('DATE(delivered_at)')->pluck('total', 'day');
        $chart = collect(range(0, $this->chartDays - 1))->map(function ($offset) use ($start, $daily) {
            $day = $start->copy()->addDays($offset);
            return ['label' => $day->format('D'), 'date' => $day->format('d M'), 'count' => (int) ($daily[$day->toDateString()] ?? 0)];
        });
        $staffCounts = (clone $dayDeliveries)->whereNotNull('delivery_staff_id')
            ->selectRaw("delivery_staff_id, COUNT(*) as total, SUM(CASE WHEN delivery_status = 'delivered' THEN 1 ELSE 0 END) as completed")
            ->groupBy('delivery_staff_id')->get()->keyBy('delivery_staff_id');
        $staff = User::deliveryStaff()->whereIn('id', $staffCounts->keys())->orderBy('name')->limit(4)->get()
            ->map(fn ($staff) => ['name' => $staff->name, 'initials' => mb_strtoupper(mb_substr($staff->name, 0, 2)), 'total' => (int) $staffCounts[$staff->id]->total, 'completed' => (int) $staffCounts[$staff->id]->completed]);

        return [
            'date' => $date,
            'collections' => [
                'today' => (float) (clone $payments)->whereDate('payment_date', $date)->sum('amount'),
                'month' => (float) (clone $payments)->whereBetween('payment_date', [$date->copy()->startOfMonth()->toDateString(), $date->toDateString()])->sum('amount'),
                'all' => (float) (clone $payments)->whereDate('payment_date', '<=', $date)->sum('amount'),
            ],
            'orders' => (clone $orders)->whereDate('order_date', $date)->count(),
            'awaiting' => (clone $orders)->where('order_status', 'pending')->whereDate('order_date', '<=', $date)->count(),
            'unassigned' => $scope(Delivery::query())->whereNull('delivery_staff_id')->where('delivery_status', 'pending')->whereDate('created_at', '<=', $date)->count(),
            'customers' => (clone $customers)->where('approval_status', 'approved')->count(),
            'dealers' => (clone $dealers)->where('approval_status', 'approved')->count(),
            'approvals' => (clone $customers)->where('approval_status', 'pending')->count(),
            'total' => $total, 'delivered' => $delivered, 'pending' => $pending, 'inProgress' => $inProgress, 'failed' => $failed, 'partial' => $partial,
            'percent' => $total ? (int) round($delivered / $total * 100) : 0,
            'chart' => $chart, 'chartMax' => max(4, (int) ceil($chart->max('count') / 4) * 4), 'staff' => $staff,
            'recent' => (clone $orders)->when(! $user?->can('orders.view'), fn ($q) => $q->whereRaw('1 = 0'))->with(['customer', 'dealer', 'zone'])->whereDate('order_date', '<=', $date)
                ->when(trim($this->search) !== '', function ($q) {
                    $term = '%'.trim($this->search).'%';
                    $q->where(fn ($q) => $q->where('order_no', 'like', $term)
                        ->orWhereHas('customer', fn ($q) => $q->where('name', 'like', $term))
                        ->orWhereHas('dealer', fn ($q) => $q->where('name', 'like', $term)));
                })->orderByDesc('order_date')->orderByDesc('id')->limit(4)->get(),
        ];
    }
}
