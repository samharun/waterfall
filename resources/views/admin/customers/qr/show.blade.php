<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer QR {{ $qrValue }}</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; font-family: Arial, sans-serif; color: #123; background: #F4FBFF; }
        .page { max-width: 920px; margin: 0 auto; padding: 32px 20px; }
        .panel { background: #fff; border: 1px solid #d8eef8; border-radius: 8px; overflow: hidden; box-shadow: 0 18px 45px rgba(0, 119, 182, .08); }
        .header { display: flex; justify-content: space-between; gap: 20px; align-items: center; padding: 24px; border-bottom: 4px solid #0077B6; }
        .brand { display: flex; gap: 12px; align-items: center; }
        .brand img { max-height: 52px; max-width: 150px; object-fit: contain; }
        .brand-text { font-size: 24px; font-weight: 700; color: #0077B6; }
        .subtitle { color: #4b6472; font-size: 13px; margin-top: 4px; }
        .content { display: grid; grid-template-columns: 1fr 330px; gap: 28px; padding: 26px; }
        .info { display: grid; gap: 12px; align-content: start; }
        .row { display: grid; grid-template-columns: 140px 1fr; gap: 14px; padding: 11px 0; border-bottom: 1px solid #eef7fb; }
        .label { color: #5f7888; font-size: 12px; text-transform: uppercase; font-weight: 700; }
        .value { color: #102a3a; font-weight: 600; line-height: 1.45; }
        .mono { font-family: Consolas, monospace; color: #0077B6; }
        .qr-card { border: 1px solid #bde8f5; border-radius: 8px; padding: 18px; text-align: center; background: linear-gradient(180deg, #fff, #fafeff); }
        .qr-box svg { width: 260px; height: 260px; max-width: 100%; }
        .qr-value { margin-top: 12px; font-family: Consolas, monospace; font-size: 17px; font-weight: 700; color: #0077B6; }
        .hint { margin-top: 8px; color: #607989; font-size: 13px; }
        .actions { display: flex; flex-wrap: wrap; gap: 10px; padding: 0 26px 26px; }
        .btn { display: inline-flex; align-items: center; justify-content: center; min-height: 38px; padding: 9px 16px; border-radius: 6px; border: 1px solid transparent; font-size: 14px; font-weight: 700; text-decoration: none; cursor: pointer; }
        .btn-primary { background: #0077B6; color: #fff; }
        .btn-secondary { background: #00B4D8; color: #fff; }
        .btn-light { background: #eef8fc; color: #17465f; border-color: #ccebf5; }
        @media (max-width: 760px) {
            .header, .content { grid-template-columns: 1fr; display: grid; }
            .row { grid-template-columns: 1fr; gap: 4px; }
            .content { padding: 18px; }
            .actions { padding: 0 18px 18px; }
        }
        @media print { .actions { display: none; } body { background: #fff; } .page { padding: 0; } .panel { box-shadow: none; border: 0; } }
    </style>
</head>
<body>
<main class="page">
    <section class="panel">
        <header class="header">
            <div class="brand">
                @if($logoUrl)
                    <img src="{{ $logoUrl }}" alt="{{ $company['name'] ?? 'Waterfall' }}">
                @endif
                <div>
                    <div class="brand-text">{{ $company['name'] ?? 'Waterfall' }}</div>
                    <div class="subtitle">Customer QR Code</div>
                </div>
            </div>
            <div class="mono">{{ $qrValue }}</div>
        </header>

        <div class="content">
            <div class="info">
                <div class="row"><div class="label">Customer Name</div><div class="value">{{ $customer->name }}</div></div>
                <div class="row"><div class="label">Customer ID</div><div class="value mono">{{ $qrValue }}</div></div>
                <div class="row"><div class="label">Mobile</div><div class="value">{{ $customer->mobile ?? '-' }}</div></div>
                <div class="row"><div class="label">Zone / Line</div><div class="value">{{ $customer->zone?->name ?? '-' }}</div></div>
                <div class="row"><div class="label">Address</div><div class="value">{{ $customer->address ?? '-' }}</div></div>
            </div>

            <aside class="qr-card">
                <div class="qr-box">{!! $qrSvg !!}</div>
                <div class="qr-value">{{ $qrValue }}</div>
                <div class="hint">Scan this QR during water delivery</div>
            </aside>
        </div>

        <div class="actions">
            <a class="btn btn-primary" href="{{ route('admin.customers.qr.print', $customer) }}" target="_blank" rel="noopener">Print QR Card</a>
            <a class="btn btn-secondary" href="{{ route('admin.customers.qr.download', $customer) }}">Download QR</a>
            <a class="btn btn-light" href="{{ url()->previous() }}">Back</a>
        </div>
    </section>
</main>
</body>
</html>
