<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Customer QR {{ $qrValue }}</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; font-family: Arial, sans-serif; color: #102a3a; background: #F4FBFF; }
        .toolbar { display: flex; justify-content: center; gap: 10px; padding: 20px; }
        .btn { min-height: 38px; padding: 9px 16px; border-radius: 6px; border: 1px solid #ccebf5; background: #fff; color: #17465f; font-weight: 700; text-decoration: none; cursor: pointer; }
        .btn-primary { background: #0077B6; color: #fff; border-color: #0077B6; }
        .sheet { display: flex; justify-content: center; padding: 12px 20px 40px; }
        .card { width: 85mm; height: 54mm; background: #fff; border: 1px solid #0077B6; border-radius: 4mm; padding: 4mm; display: grid; grid-template-rows: auto 1fr auto; gap: 2mm; overflow: hidden; }
        .top { display: flex; justify-content: space-between; align-items: start; gap: 3mm; border-bottom: .6mm solid #00B4D8; padding-bottom: 2mm; }
        .brand { color: #0077B6; font-weight: 800; font-size: 12pt; line-height: 1.1; }
        .title { color: #4f6978; font-size: 7pt; text-transform: uppercase; font-weight: 700; text-align: right; }
        .middle { display: grid; grid-template-columns: 26mm 1fr; gap: 4mm; align-items: center; min-height: 0; }
        .qr svg { width: 26mm; height: 26mm; display: block; }
        .details { min-width: 0; }
        .customer-id { font-family: Consolas, monospace; color: #0077B6; font-size: 10pt; font-weight: 800; overflow-wrap: anywhere; }
        .name { margin-top: 1.6mm; font-size: 9pt; font-weight: 700; line-height: 1.15; }
        .mobile, .address { margin-top: 1mm; font-size: 7pt; color: #405968; line-height: 1.2; }
        .footer { border-top: .2mm solid #d7edf6; padding-top: 1.5mm; color: #557382; font-size: 7pt; display: flex; justify-content: space-between; gap: 4mm; }
        @page { size: A4; margin: 8mm; }
        @media print {
            body { background: #fff; }
            .toolbar { display: none !important; }
            .sheet { padding: 0; display: block; }
            .card { page-break-inside: avoid; break-inside: avoid; }
        }
    </style>
</head>
<body>
<div class="toolbar">
    <button class="btn btn-primary" onclick="window.print()">Print</button>
    <a class="btn" href="{{ route('admin.customers.qr.show', $customer) }}">Back</a>
</div>

<main class="sheet">
    <section class="card">
        <header class="top">
            <div class="brand">{{ $company['name'] ?? 'Waterfall' }}</div>
            <div class="title">Customer QR Card</div>
        </header>

        <div class="middle">
            <div class="qr">{!! $qrSvg !!}</div>
            <div class="details">
                <div class="customer-id">{{ $qrValue }}</div>
                <div class="name">{{ $customer->name }}</div>
                <div class="mobile">{{ $customer->mobile ?? '-' }}</div>
                @if($customer->address)
                    <div class="address">{{ \Illuminate\Support\Str::limit($customer->address, 72) }}</div>
                @endif
            </div>
        </div>

        <footer class="footer">
            <span>Scan this QR during water delivery</span>
            <strong>Pure Drinking Water Delivery</strong>
        </footer>
    </section>
</main>
</body>
</html>
