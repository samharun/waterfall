<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Services\SettingsService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class CustomerQrController extends Controller
{
    public function show(Customer $customer)
    {
        $this->authorizeQrAccess();

        return view('admin.customers.qr.show', $this->viewData($customer));
    }

    public function download(Customer $customer)
    {
        $this->authorizeQrAccess();

        $qrValue = $customer->qrValue();
        $svg = $this->makeQrSvg($qrValue, 420);
        $filename = 'waterfall-qr-'.Str::slug($qrValue).'.svg';

        return response($svg, 200, [
            'Content-Type' => 'image/svg+xml',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    public function print(Customer $customer)
    {
        $this->authorizeQrAccess();

        return view('admin.customers.qr.print', $this->viewData($customer));
    }

    public function bulkPrint(Request $request)
    {
        $this->authorizeQrAccess();

        $customers = $this->bulkCustomers($request);
        $settings = app(SettingsService::class);

        return view('admin.customers.qr.bulk-print', [
            'customers' => $customers->map(fn (Customer $customer) => [
                'customer' => $customer,
                'qrValue' => $customer->qrValue(),
                'qrSvg' => $this->makeQrSvg($customer->qrValue(), 180),
            ]),
            'company' => $settings->company(),
            'branding' => $settings->branding(),
            'logoUrl' => $settings->logoUrl(),
            'filters' => $request->only(['zone_id', 'approval_status']),
        ]);
    }

    protected function viewData(Customer $customer): array
    {
        $customer->loadMissing('zone');

        $settings = app(SettingsService::class);
        $qrValue = $customer->qrValue();

        return [
            'customer' => $customer,
            'qrValue' => $qrValue,
            'qrSvg' => $this->makeQrSvg($qrValue, 260),
            'company' => $settings->company(),
            'branding' => $settings->branding(),
            'logoUrl' => $settings->logoUrl(),
        ];
    }

    protected function bulkCustomers(Request $request): Collection
    {
        $ids = $this->customerIdsFromRequest($request);

        return Customer::query()
            ->with('zone')
            ->when($ids !== [], fn ($query) => $query->whereIn('id', $ids))
            ->when($ids === [] && $request->filled('zone_id'), fn ($query) => $query->where('zone_id', $request->integer('zone_id')))
            ->when($ids === [] && $request->filled('approval_status'), fn ($query) => $query->where('approval_status', (string) $request->string('approval_status')))
            ->orderBy('customer_id')
            ->limit(300)
            ->get();
    }

    protected function customerIdsFromRequest(Request $request): array
    {
        $ids = $request->input('customer_ids', []);

        if (is_string($ids)) {
            $ids = explode(',', $ids);
        }

        if (! is_array($ids)) {
            return [];
        }

        return collect($ids)
            ->flatMap(fn ($id) => is_string($id) && str_contains($id, ',') ? explode(',', $id) : [$id])
            ->map(fn ($id) => (int) $id)
            ->filter(fn (int $id) => $id > 0)
            ->unique()
            ->values()
            ->all();
    }

    protected function makeQrSvg(string $value, int $size): string
    {
        return (string) QrCode::format('svg')
            ->size($size)
            ->margin(1)
            ->generate($value);
    }

    protected function authorizeQrAccess(): void
    {
        abort_unless(auth()->user()?->can('customers.view'), 403);
    }
}
