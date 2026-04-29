<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bulk Customer QR Print</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; font-family: Arial, sans-serif; color: #102a3a; background: #F4FBFF; }
        .toolbar { display: flex; justify-content: space-between; align-items: center; gap: 16px; padding: 18px 22px; background: #fff; border-bottom: 1px solid #d8eef8; }
        .toolbar h1 { margin: 0; color: #0077B6; font-size: 20px; }
        .toolbar p { margin: 4px 0 0; color: #597585; font-size: 13px; }
        .actions { display: flex; gap: 10px; flex-wrap: wrap; }
        .btn { min-height: 38px; padding: 9px 16px; border-radius: 6px; border: 1px solid #ccebf5; background: #fff; color: #17465f; font-weight: 700; text-decoration: none; cursor: pointer; }
        .btn-primary { background: #0077B6; color: #fff; border-color: #0077B6; }
        .sheet { max-width: 210mm; margin: 0 auto; padding: 10mm; }
        .grid { display: grid; grid-template-columns: repeat(2, 85mm); gap: 7mm; align-items: start; justify-content: center; }
        .card { width: 85mm; height: 54mm; background: #fff; border: 1px solid #0077B6; border-radius: 4mm; padding: 4mm; display: grid; grid-template-rows: auto 1fr auto; gap: 2mm; overflow: hidden; break-inside: avoid; page-break-inside: avoid; }
        .top { display: flex; justify-content: space-between; gap: 3mm; border-bottom: .6mm solid #00B4D8; padding-bottom: 2mm; }
        .brand { color: #0077B6; font-weight: 800; font-size: 11pt; line-height: 1.1; }
        .zone { color: #4f6978; font-size: 7pt; font-weight: 700; text-align: right; }
        .middle { display: grid; grid-template-columns: 24mm 1fr; gap: 4mm; align-items: center; min-height: 0; }
        .qr svg { width: 24mm; height: 24mm; display: block; }
        .customer-id { font-family: Consolas, monospace; color: #0077B6; font-size: 9pt; font-weight: 800; overflow-wrap: anywhere; }
        .name { margin-top: 1.3mm; font-size: 8.5pt; font-weight: 700; line-height: 1.15; }
        .mobile, .address { margin-top: .9mm; font-size: 7pt; color: #405968; line-height: 1.2; }
        .footer { border-top: .2mm solid #d7edf6; padding-top: 1.4mm; color: #557382; font-size: 7pt; display: flex; justify-content: space-between; gap: 4mm; }
        .empty { padding: 40px; text-align: center; background: #fff; border: 1px solid #d8eef8; border-radius: 8px; color: #597585; }
        @page { size: A4; margin: 8mm; }
        @media print {
            body { background: #fff; }
            .toolbar { display: none !important; }
            .sheet { padding: 0; margin: 0; max-width: none; }
            .grid { gap: 6mm; }
        }
    </style>
</head>
<body>
<header class="toolbar">
    <div>
        <h1>{{ $company['name'] ?? 'Waterfall' }} Customer QR Cards</h1>
        <p>{{ $customers->count() }} customer{{ $customers->count() === 1 ? '' : 's' }} ready for printing</p>
    </div>
    <div class="actions">
        <button class="btn btn-primary" onclick="window.print()">Print</button>
        <a class="btn" href="{{ url('/admin/customers') }}">Back to Customers</a>
    </div>
</header>

<main class="sheet">
    @if($customers->isEmpty())
        <div class="empty">No customers matched this QR print request.</div>
    @else
        <section class="grid">
            @foreach($customers as $item)
                @php($customer = $item['customer'])
                <article class="card">
                    <header class="top">
                        <div class="brand">{{ $company['name'] ?? 'Waterfall' }}</div>
                        <div class="zone">{{ $customer->zone?->name ?? 'Customer QR' }}</div>
                    </header>

                    <div class="middle">
                        <div class="qr">{!! $item['qrSvg'] !!}</div>
                        <div>
                            <div class="customer-id">{{ $item['qrValue'] }}</div>
                            <div class="name">{{ $customer->name }}</div>
                            <div class="mobile">{{ $customer->mobile ?? '-' }}</div>
                            @if($customer->address)
                                <div class="address">{{ \Illuminate\Support\Str::limit($customer->address, 56) }}</div>
                            @endif
                        </div>
                    </div>

                    <footer class="footer">
                        <span>Scan during delivery</span>
                        <strong>Pure Drinking Water Delivery</strong>
                    </footer>
                </article>
            @endforeach
        </section>
    @endif
</main>
</body>
</html>
