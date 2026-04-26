<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\Customer\Concerns\ApiResponse;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $locale   = $this->resolveLocale($request);
        $customer = $request->user()->load('zone.deliveryManager')->fresh(); // fresh() ensures current_due is up-to-date
        $customer->loadMissing('zone.deliveryManager');

        $latestOrder = Order::forCustomer($customer->id)
            ->with(['items.product', 'delivery'])
            ->withSum('items as total_quantity', 'quantity')
            ->latest('order_date')
            ->first();

        $activeOrders = Order::forCustomer($customer->id)
            ->whereIn('order_status', ['pending', 'confirmed', 'assigned'])
            ->count();

        $totalOrders = Order::forCustomer($customer->id)->count();

        $latestInvoice = Invoice::forCustomer($customer->id)
            ->whereNotIn('invoice_status', ['cancelled', 'draft'])
            ->latest('invoice_date')
            ->first();

        return $this->success('Dashboard data retrieved.', [
            'customer' => [
                'name'            => $customer->name,
                'name_bn'         => $customer->name_bn,
                'display_name'    => $this->localized($customer->name_bn, $customer->name, $locale),
                'customer_id'     => $customer->customer_id,
                'mobile'          => $customer->mobile,
                'zone'            => $customer->zone?->name,
                'customer_type'       => $customer->customer_type,
                'customer_type_label' => $this->translateType($customer->customer_type, $locale),
            ],
            'stats' => [
                'total_orders'  => $totalOrders,
                'active_orders' => $activeOrders,
                'current_due'   => (float) $customer->current_due,
                'jar_balance'   => $customer->jar_deposit_qty ?? 0,
            ],
            'latest_order' => $latestOrder ? [
                'id'                  => $latestOrder->id,
                'order_no'            => $latestOrder->order_no,
                'order_date'          => $latestOrder->order_date?->toDateString(),
                'order_status'        => $latestOrder->order_status,
                'order_status_label'  => $this->translateStatus($latestOrder->order_status, $locale),
                'quantity'            => $latestOrder->totalQuantity(),
                'total_quantity'      => $latestOrder->totalQuantity(),
                'total_amount'        => (float) $latestOrder->total_amount,
                'delivery_status'     => $latestOrder->delivery?->delivery_status,
                'delivery_status_label' => $latestOrder->delivery
                    ? $this->translateStatus($latestOrder->delivery->delivery_status, $locale)
                    : null,
            ] : null,
            'latest_bill' => $latestInvoice ? [
                'invoice_no'    => $latestInvoice->invoice_no,
                'billing_month' => $latestInvoice->billing_month,
                'billing_year'  => $latestInvoice->billing_year,
                'total_amount'  => (float) $latestInvoice->total_amount,
                'due_amount'    => (float) $latestInvoice->due_amount,
                'status'        => $latestInvoice->invoice_status,
                'status_label'  => $this->translateStatus($latestInvoice->invoice_status, $locale),
            ] : null,
            'emergency_contact' => $this->buildEmergencyContact($customer),
        ]);
    }

    public function emergencyContact(Request $request): JsonResponse
    {
        /** @var Customer $customer */
        $customer = $request->user()->load('zone.deliveryManager');

        return $this->success('Emergency contact retrieved.', [
            'emergency_contact' => $this->buildEmergencyContact($customer),
        ]);
    }

    private function buildEmergencyContact(Customer $customer): ?array
    {
        $zone = $customer->zone;
        $manager = $zone?->deliveryManager;

        if (! $zone || ! $manager) {
            return null;
        }

        return [
            'name' => $manager->name,
            'email' => $manager->email,
            'phone' => $manager->mobile,
            'mobile' => $manager->mobile,
            'designation' => 'Delivery Manager',
            'zone' => [
                'id' => $zone->id,
                'name' => $zone->name,
                'code' => $zone->code,
            ],
        ];
    }
}
