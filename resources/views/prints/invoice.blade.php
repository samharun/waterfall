<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice {{ $invoice->invoice_no }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: Arial, sans-serif; font-size: 13px; color: #1a202c; background: #fff; }
        .page { max-width: 800px; margin: 0 auto; padding: 30px; }

        /* Header */
        .header { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 3px solid {{ $branding['primary_color'] ?? '#0ea5e9' }}; padding-bottom: 16px; margin-bottom: 20px; }
        .company-logo img { max-height: 60px; max-width: 160px; object-fit: contain; }
        .company-logo .text-logo { font-size: 22px; font-weight: 700; color: {{ $branding['primary_color'] ?? '#0ea5e9' }}; }
        .company-info { text-align: right; font-size: 12px; color: #475569; line-height: 1.6; }
        .company-info .company-name { font-size: 16px; font-weight: 700; color: #1e293b; }
        .company-info .tagline { font-size: 11px; color: #94a3b8; }

        /* Document title */
        .doc-title { text-align: center; margin: 16px 0; }
        .doc-title h1 { font-size: 22px; font-weight: 700; color: {{ $branding['primary_color'] ?? '#0ea5e9' }}; letter-spacing: 3px; }

        /* Two-column info */
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px; }
        .info-box { background: #f8fafc; border-radius: 6px; padding: 12px; }
        .info-box h3 { font-size: 11px; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 8px; }
        .info-row { display: flex; justify-content: space-between; padding: 3px 0; font-size: 12px; border-bottom: 1px solid #f1f5f9; }
        .info-row:last-child { border-bottom: none; }
        .info-label { color: #64748b; }
        .info-value { font-weight: 600; text-align: right; }

        /* Status badge */
        .status-badge { display: inline-block; padding: 2px 10px; border-radius: 20px; font-size: 11px; font-weight: 700; text-transform: uppercase; }
        .status-draft    { background: #f1f5f9; color: #475569; }
        .status-issued   { background: #dbeafe; color: #1e40af; }
        .status-partial  { background: #fef3c7; color: #92400e; }
        .status-paid     { background: #d1fae5; color: #065f46; }
        .status-cancelled{ background: #fee2e2; color: #991b1b; }

        /* Amount table */
        .amount-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .amount-table th { background: {{ $branding['primary_color'] ?? '#0ea5e9' }}; color: #fff; padding: 8px 12px; text-align: left; font-size: 12px; }
        .amount-table td { padding: 8px 12px; border-bottom: 1px solid #f1f5f9; font-size: 12px; }
        .amount-table tr:last-child td { border-bottom: none; }
        .amount-table .total-row td { font-weight: 700; font-size: 14px; background: #f8fafc; }
        .amount-table .due-row td { color: #ef4444; font-weight: 700; }
        .text-right { text-align: right; }

        /* Payments */
        .payments-section { margin-bottom: 20px; }
        .payments-section h3 { font-size: 13px; font-weight: 700; margin-bottom: 8px; color: #1e293b; }
        .payments-table { width: 100%; border-collapse: collapse; font-size: 12px; }
        .payments-table th { background: #f1f5f9; padding: 6px 10px; text-align: left; font-size: 11px; color: #64748b; }
        .payments-table td { padding: 6px 10px; border-bottom: 1px solid #f8fafc; }

        /* Footer */
        .footer { border-top: 1px solid #e2e8f0; padding-top: 14px; margin-top: 20px; text-align: center; font-size: 11px; color: #94a3b8; line-height: 1.8; }

        /* Print controls */
        .print-controls { text-align: center; margin: 20px 0; }
        .btn { display: inline-block; padding: 8px 20px; border-radius: 6px; font-size: 13px; font-weight: 600; cursor: pointer; border: none; margin: 0 4px; }
        .btn-print { background: {{ $branding['primary_color'] ?? '#0ea5e9' }}; color: #fff; }
        .btn-back  { background: #f1f5f9; color: #374151; text-decoration: none; }

        @media print {
            .print-controls { display: none !important; }
            body { font-size: 12px; }
            .page { padding: 15px; }
        }
    </style>
</head>
<body>
<div class="page">

    {{-- Print Controls --}}
    <div class="print-controls">
        @if($context === 'admin')
            <a href="javascript:history.back()" class="btn btn-back">← Back</a>
        @elseif($context === 'customer')
            <a href="{{ route('customer.invoices.show', $invoice) }}" class="btn btn-back">← Back</a>
        @elseif($context === 'dealer')
            <a href="{{ route('dealer.invoices.show', $invoice) }}" class="btn btn-back">← Back</a>
        @endif
        <button onclick="window.print()" class="btn btn-print">🖨 Print</button>
    </div>

    {{-- Header --}}
    <div class="header">
        <div class="company-logo">
            @if($logoUrl)
                <img src="{{ $logoUrl }}" alt="{{ $company['name'] ?? 'Waterfall' }}">
            @else
                <div class="text-logo">💧 {{ $company['name'] ?? 'Waterfall' }}</div>
            @endif
            @if($company['tagline'] ?? '')
                <div style="font-size:11px;color:#94a3b8;margin-top:3px;">{{ $company['tagline'] }}</div>
            @endif
        </div>
        <div class="company-info">
            <div class="company-name">{{ $company['name'] ?? 'Waterfall' }}</div>
            @if($company['legal_name'] ?? '') <div>{{ $company['legal_name'] }}</div> @endif
            @if($company['address'] ?? '') <div>{{ $company['address'] }}</div> @endif
            @if($company['mobile'] ?? '') <div>📞 {{ $company['mobile'] }}</div> @endif
            @if($company['email'] ?? '') <div>✉ {{ $company['email'] }}</div> @endif
            @if($company['website'] ?? '') <div>🌐 {{ $company['website'] }}</div> @endif
            @if($company['trade_license_no'] ?? '') <div>TL: {{ $company['trade_license_no'] }}</div> @endif
            @if($company['bin_no'] ?? '') <div>BIN: {{ $company['bin_no'] }}</div> @endif
        </div>
    </div>

    {{-- Document Title --}}
    <div class="doc-title">
        <h1>INVOICE</h1>
    </div>

    {{-- Invoice & Bill-To Info --}}
    <div class="info-grid">
        <div class="info-box">
            <h3>Invoice Details</h3>
            <div class="info-row"><span class="info-label">Invoice No</span><span class="info-value" style="font-family:monospace;">{{ $invoice->invoice_no }}</span></div>
            <div class="info-row"><span class="info-label">Invoice Date</span><span class="info-value">{{ $invoice->invoice_date?->format('d M Y') }}</span></div>
            @if($invoice->due_date)
            <div class="info-row"><span class="info-label">Due Date</span><span class="info-value">{{ $invoice->due_date->format('d M Y') }}</span></div>
            @endif
            @if($invoice->billing_month && $invoice->billing_year)
            <div class="info-row"><span class="info-label">Billing Period</span><span class="info-value">{{ \Carbon\Carbon::create($invoice->billing_year, $invoice->billing_month)->format('F Y') }}</span></div>
            @endif
            <div class="info-row">
                <span class="info-label">Status</span>
                <span class="status-badge status-{{ $invoice->invoice_status }}">{{ ucfirst($invoice->invoice_status) }}</span>
            </div>
        </div>

        <div class="info-box">
            <h3>Bill To</h3>
            @php
                $party = $invoice->invoice_type === 'customer' ? $invoice->customer : $invoice->dealer;
                $code  = $invoice->invoice_type === 'customer' ? ($party?->customer_id ?? '') : ($party?->dealer_code ?? '');
                $zone  = $party?->zone?->name ?? '';
            @endphp
            <div class="info-row"><span class="info-label">{{ ucfirst($invoice->invoice_type) }} ID</span><span class="info-value" style="font-family:monospace;">{{ $code }}</span></div>
            <div class="info-row"><span class="info-label">Name</span><span class="info-value">{{ $party?->name ?? '—' }}</span></div>
            <div class="info-row"><span class="info-label">Mobile</span><span class="info-value">{{ $party?->mobile ?? '—' }}</span></div>
            @if($zone)<div class="info-row"><span class="info-label">Zone</span><span class="info-value">{{ $zone }}</span></div>@endif
            @if($party?->address)<div class="info-row"><span class="info-label">Address</span><span class="info-value" style="max-width:180px;text-align:right;">{{ $party->address }}</span></div>@endif
        </div>
    </div>

    {{-- Amount Summary --}}
    <table class="amount-table">
        <thead>
            <tr><th>Description</th><th class="text-right">Amount ({{ $billing['currency'] ?? 'BDT' }})</th></tr>
        </thead>
        <tbody>
            <tr><td>Subtotal</td><td class="text-right">{{ number_format((float)$invoice->subtotal, 2) }}</td></tr>
            <tr><td>Previous Due</td><td class="text-right">{{ number_format((float)$invoice->previous_due, 2) }}</td></tr>
            <tr class="total-row"><td>Total Amount</td><td class="text-right">{{ number_format((float)$invoice->total_amount, 2) }}</td></tr>
            <tr><td>Paid Amount</td><td class="text-right" style="color:#10b981;">{{ number_format((float)$invoice->paid_amount, 2) }}</td></tr>
            <tr class="{{ (float)$invoice->due_amount > 0 ? 'due-row' : '' }}">
                <td>Due Amount</td>
                <td class="text-right">{{ number_format((float)$invoice->due_amount, 2) }}</td>
            </tr>
        </tbody>
    </table>

    {{-- Payments --}}
    @if($invoice->payments->count())
    <div class="payments-section">
        <h3>Payment History</h3>
        <table class="payments-table">
            <thead>
                <tr>
                    <th>Payment No</th><th>Date</th><th>Method</th><th>Reference</th><th class="text-right">Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->payments as $pay)
                <tr>
                    <td style="font-family:monospace;">{{ $pay->payment_no }}</td>
                    <td>{{ $pay->payment_date?->format('d M Y') }}</td>
                    <td>{{ ucfirst($pay->payment_method) }}</td>
                    <td>{{ $pay->reference_no ?? '—' }}</td>
                    <td class="text-right">{{ number_format((float)$pay->amount, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    @if($invoice->remarks)
    <div style="background:#f8fafc;border-radius:6px;padding:10px;margin-bottom:16px;font-size:12px;color:#475569;">
        <strong>Remarks:</strong> {{ $invoice->remarks }}
    </div>
    @endif

    {{-- Footer --}}
    <div class="footer">
        @if($branding['invoice_footer_note'] ?? '')
            <div style="font-weight:600;color:#475569;margin-bottom:4px;">{{ $branding['invoice_footer_note'] }}</div>
        @endif
        <div>This is a system generated invoice. — {{ $company['name'] ?? 'Waterfall' }}</div>
        @if($company['support_mobile'] ?? '') <div>Support: {{ $company['support_mobile'] }}</div> @endif
    </div>

</div>
</body>
</html>
