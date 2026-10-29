<?php

namespace App\Filament\Admin\Pages;

use App\Models\{Customer, Dealer, Delivery, Invoice, JarDeposit, Order, Payment, Product, User, Zone};
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Filament\Notifications\Notification;
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
    public string $zoneFilter = '';
    public ?int $assignmentOrderId = null;
    public ?int $assignmentStaffId = null;
    public string $queueFilter = '';

    public function getAvailableZones()
    {
        $user = auth()->user();
        return Zone::query()->when($user?->role === 'delivery_manager', fn ($q) => $q->whereIn('id',
            $user->managedZones()->pluck('id')->merge($user->zones()->pluck('zones.id'))->unique()
        ))->orderBy('name')->get(['id', 'name']);
    }

    private function scopedZones(): ?array
    {
        $available = $this->getAvailableZones()->modelKeys();
        if ($this->zoneFilter !== '') {
            abort_unless(ctype_digit($this->zoneFilter) && in_array((int) $this->zoneFilter, $available, true), 403);
            return [(int) $this->zoneFilter];
        }
        return auth()->user()?->role === 'delivery_manager' ? $available : null;
    }

    private function actionableOrder(int $id): Order
    {
        $zones = $this->scopedZones();
        return Order::query()->when($zones !== null, fn ($q) => $q->whereIn('zone_id', $zones))->lockForUpdate()->findOrFail($id);
    }

    public function confirmOrder(int $id): void
    {
        abort_unless(auth()->user()?->can('orders.confirm'), 403);
        DB::transaction(function () use ($id) {
            $order = $this->actionableOrder($id);
            if ($order->order_status !== 'pending') {
                throw ValidationException::withMessages(['operation' => 'This order is no longer pending. Refresh and try again.']);
            }
            $order->markConfirmed();
        });
        Notification::make()->title('Order confirmed')->success()->send();
        $this->refreshDashboard();
    }

    public function openAssignment(int $id): void
    {
        abort_unless(auth()->user()?->can('deliveries.assign'), 403);
        $order = $this->actionableOrder($id);
        abort_unless($order->order_status === 'confirmed' && $order->deliveries()->where('delivery_status', 'pending')->exists(), 409);
        $this->resetValidation();
        $this->assignmentOrderId = $id;
        $this->assignmentStaffId = null;
        $this->queueFilter = '';
    }

    public function getAssignableStaff()
    {
        $zones = $this->scopedZones();
        return User::deliveryStaff()->when($zones !== null, fn ($q) => $q->whereHas('zones', fn ($q) => $q->whereIn('zones.id', $zones)))
            ->orderBy('name')->get(['id', 'name']);
    }

    public function assignStaff(): void
    {
        abort_unless(auth()->user()?->can('deliveries.assign'), 403);
        $this->validate(['assignmentOrderId' => 'required|integer', 'assignmentStaffId' => 'required|integer']);
        abort_unless($this->getAssignableStaff()->contains('id', $this->assignmentStaffId), 403);
        DB::transaction(function () {
            $order = $this->actionableOrder($this->assignmentOrderId);
            $delivery = $order->deliveries()->where('delivery_status', 'pending')->whereNull('delivery_staff_id')->lockForUpdate()->first();
            if ($order->order_status !== 'confirmed' || ! $delivery) {
                throw ValidationException::withMessages(['assignmentStaffId' => 'This delivery has changed. Refresh before assigning.']);
            }
            $delivery->markAssigned($this->assignmentStaffId, auth()->id());
        });
        $this->assignmentOrderId = null;
        $this->assignmentStaffId = null;
        Notification::make()->title('Delivery staff assigned')->success()->send();
        $this->refreshDashboard();
    }

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
        $zones = $this->scopedZones();
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

        return array_merge([
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
        ], $this->operationalData($date, $zones, $orders, $customers, $dealers, $payments));
    }

    private function operationalData($date, ?array $zones, $orders, $customers, $dealers, $payments): array
    {
        $scope = fn ($q) => $q->when($zones !== null, fn ($q) => $q->whereIn('zone_id', $zones));
        $partyScope = fn ($q) => $q->when($zones !== null, fn ($q) => $q->where(fn ($q) => $q
            ->whereHas('customer', fn ($q) => $q->whereIn('zone_id', $zones))
            ->orWhereHas('dealer', fn ($q) => $q->whereIn('zone_id', $zones))));
        // Backlogs and balances are current; a historical date never reconstructs past statuses.
        $active = $scope(Delivery::query())->whereIn('delivery_status', ['pending', 'assigned', 'in_progress', 'partial_delivered'])
            ->whereHas('order', fn ($q) => $q->whereNotIn('order_status', ['cancelled', 'delivered']));
        $overdue = (clone $active)->whereHas('order', fn ($q) => $q->where(fn ($q) => $q
            ->where('preferred_delivery_time', '<', now())
            ->orWhere(fn ($q) => $q->whereNull('preferred_delivery_time')->whereDate('order_date', '<', today()))));
        $exceptions = $scope(Delivery::query())->whereIn('delivery_status', ['failed', 'not_delivered', 'customer_unavailable', 'partial_delivered'])
            ->whereHas('order', fn ($q) => $q->whereNotIn('order_status', ['cancelled', 'delivered']));
        $sources = (clone $orders)->whereDate('order_date', $date)->selectRaw('source_channel, COUNT(*) as total')->groupBy('source_channel')->pluck('total', 'source_channel');
        $todayAmount = (float) (clone $payments)->whereDate('payment_date', $date)->sum('amount');
        $yesterdayAmount = (float) (clone $payments)->whereDate('payment_date', $date->copy()->subDay())->sum('amount');
        $canFinance = auth()->user()->can('due_collections.manage');
        $invoices = $partyScope(Invoice::query())->whereIn('invoice_status', ['issued', 'partial', 'overdue'])->where('due_amount', '>', 0)->whereDate('due_date', '<', today());
        $jars = $partyScope(JarDeposit::query());
        $jarTotals = (clone $jars)->selectRaw("party_type, SUM(CASE WHEN transaction_type IN ('deposit_received', 'jar_issued') THEN quantity WHEN transaction_type = 'jar_returned' THEN -quantity WHEN transaction_type = 'adjustment' AND adjustment_direction = 'decrease' THEN -quantity WHEN transaction_type = 'adjustment' THEN quantity ELSE 0 END) as total")
            ->groupBy('party_type')->pluck('total', 'party_type');
        $products = auth()->user()->can('products.view') ? Product::active()->orderBy('name')->get() : collect();
        $workload = $scope(Delivery::query())->whereIn('delivery_status', ['assigned', 'in_progress', 'partial_delivered'])->whereNotNull('delivery_staff_id')
            ->selectRaw('delivery_staff_id, COUNT(*) as remaining')->groupBy('delivery_staff_id')->pluck('remaining', 'delivery_staff_id');
        $completedByStaff = $scope(Delivery::query())->where('delivery_status', 'delivered')->whereDate('delivered_at', $date)
            ->selectRaw('delivery_staff_id, COUNT(*) as total')->groupBy('delivery_staff_id')->pluck('total', 'delivery_staff_id');
        $staff = $this->getAssignableStaff()->map(function ($person) use ($workload, $completedByStaff) {
            $done = (int) ($completedByStaff[$person->id] ?? 0);
            return ['id' => $person->id, 'name' => $person->name, 'initials' => mb_strtoupper(mb_substr($person->name, 0, 2)), 'completed' => $done, 'remaining' => (int) ($workload[$person->id] ?? 0), 'total' => $done + (int) ($workload[$person->id] ?? 0)];
        })->sortByDesc('remaining')->take(4);
        $zoneCounts = (clone $orders)->whereDate('order_date', $date)->where('order_status', '!=', 'cancelled')
            ->selectRaw("zone_id, COUNT(*) as total, SUM(CASE WHEN order_status = 'delivered' THEN 1 ELSE 0 END) as completed")->groupBy('zone_id')->get()->keyBy('zone_id');
        $zoneStats = $this->getAvailableZones()->when($this->zoneFilter !== '', fn ($c) => $c->where('id', (int) $this->zoneFilter))->map(function ($zone) use ($zoneCounts) {
            $total = (int) ($zoneCounts[$zone->id]->total ?? 0);
            $done = (int) ($zoneCounts[$zone->id]->completed ?? 0);
            return ['name' => $zone->name, 'orders' => $total, 'completed' => $done, 'percent' => $total ? (int) round($done / $total * 100) : null];
        })->sortByDesc('orders')->take(5);
        $activity = collect();
        if (auth()->user()->can('orders.view')) {
            foreach ((clone $orders)->latest('updated_at')->limit(4)->get() as $order) {
                $activity->push(['title' => $order->order_no.' · '.ucfirst($order->order_status), 'detail' => 'Order record updated', 'time' => $order->updated_at, 'icon' => 'shopping-cart', 'color' => 'blue']);
            }
        }
        if (auth()->user()->can('deliveries.view')) {
            foreach ($scope(Delivery::query())->with('deliveryStaff')->latest('updated_at')->limit(4)->get() as $delivery) {
                $activity->push(['title' => $delivery->delivery_no.' · '.ucwords(str_replace('_', ' ', $delivery->delivery_status)), 'detail' => $delivery->deliveryStaff?->name ?? 'No staff assigned', 'time' => $delivery->updated_at, 'icon' => 'truck', 'color' => 'green']);
            }
        }
        if ($canFinance) {
            foreach ((clone $payments)->latest('created_at')->limit(4)->get() as $payment) {
                $activity->push(['title' => 'Payment ৳ '.number_format($payment->amount, 2).' recorded', 'detail' => $payment->payment_no, 'time' => $payment->created_at, 'icon' => 'banknotes', 'color' => 'violet']);
            }
        }
        $queue = collect();
        if ($this->queueFilter !== '' && auth()->user()->can('orders.view')) {
            $query = match ($this->queueFilter) {
                'overdue' => (clone $orders)->whereHas('deliveries', fn ($q) => $q->whereIn('id', (clone $overdue)->select('id'))),
                'exceptions' => (clone $orders)->whereHas('deliveries', fn ($q) => $q->whereIn('id', (clone $exceptions)->select('id'))),
                'pending' => (clone $orders)->where('order_status', 'pending'),
                'unassigned' => (clone $orders)->where('order_status', 'confirmed')->whereHas('deliveries', fn ($q) => $q->where('delivery_status', 'pending')->whereNull('delivery_staff_id')),
                default => null,
            };
            $queue = $query?->with(['customer', 'dealer'])->orderBy('order_date')->limit(20)->get() ?? collect();
        }
        return [
            'queue' => $queue,
            'zones' => $this->getAvailableZones(), 'sources' => $sources, 'yesterday' => $yesterdayAmount,
            'growth' => $yesterdayAmount > 0 ? round(($todayAmount - $yesterdayAmount) / $yesterdayAmount * 100, 1) : null,
            'overdue' => $overdue->count(), 'exceptions' => $exceptions->count(),
            'awaiting' => (clone $orders)->where('order_status', 'pending')->count(),
            'unassigned' => $scope(Delivery::query())->whereNull('delivery_staff_id')->where('delivery_status', 'pending')->whereHas('order', fn ($q) => $q->where('order_status', 'confirmed'))->count(),
            'dues' => $canFinance ? ['customers' => (float) (clone $customers)->sum('current_due'), 'dealers' => (float) (clone $dealers)->sum('current_due'), 'overdueInvoices' => (float) (clone $invoices)->sum('due_amount'), 'invoiceCount' => (clone $invoices)->count()] : null,
            'jars' => auth()->user()->can('jar_deposits.view') ? ['customers' => max(0, (int) ($jarTotals['customer'] ?? 0)), 'dealers' => max(0, (int) ($jarTotals['dealer'] ?? 0)), 'returned' => (int) (clone $jars)->whereDate('transaction_date', $date)->where('transaction_type', 'jar_returned')->sum('quantity')] : null,
            'stock' => $products->take(3), 'lowStock' => $products->filter(fn ($p) => $p->current_stock <= $p->stock_alert_qty)->count(),
            'staff' => auth()->user()->can('deliveries.view') ? $staff : collect(),
            'zoneStats' => auth()->user()->can('orders.view') ? $zoneStats : collect(),
            'activity' => $activity->sortByDesc('time')->take(4)->values(),
        ];
    }
}
