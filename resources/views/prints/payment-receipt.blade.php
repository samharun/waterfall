<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt {{ $payment->payment_no }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: Arial, sans-serif; font-size: 13px; color: #1a202c; background: #fff; }
        .page { max-width: 600px; margin: 0 auto; padding: 30px; }
        .header { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 3px solid {{ $branding['primary_color'] ?? '#0ea5e9' }}; padding-bottom: 14px; margin-bottom: 18px; }
        .company-logo .text-logo { font-size: 20px; font-weight: 700; color: {{ $branding['primary_color'] ?? '#0ea5e9' }}; }
        .company-info { text-align: right; font-size: 11px; color: #475569; line-height: 1.6; }
        .company-info .company-name { font-size: 15px; font-weight: 700; color: #1e293b; }
        .doc-title { text-align: center; margin: 14px 0; }
        .doc-title h1 { font-size: 20px; font-weight: 700; color: {{ $branding['primary_color'] ?? '#0ea5e9' }}; letter-spacing: 3px; }
        .info-box { background: #f8fafc; border-radius: 6px; padding: 12px; margin-bottom: 14px; }
        .info-box h3 { font-size: 11px; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 8px; }
        .info-row { display: flex; justify-content: space-between; padding: 4px 0; font-size: 12px; border-bottom: 1px solid #f1f5f9; }
        .info-row:last-child { border-bottom: none; }
        .info-label { color: #64748b; }
        .info-value { font-weight: 600; text-align: right; }
        .amount-box { background: {{ $branding['primary_color'] ?? '#0ea5e9' }}; color: #fff; border-radius: 8px; padding: 16px; text-align: center; margin: 16px 0; }
        .amount-box .label { font-size: 12px; opacity: .85; margin-bottom: 4px; }
        .amount-box .amount { font-size: 28px; font-weight: 700; }
        .footer { border-top: 1px solid #e2e8f0; padding-top: 12px; margin-top: 16px; text-align: center; font-size: 11px; color: #94a3b8; line-height: 1.8; }
        .print-controls { text-align: center; margin: 16px 0; }
        .btn { display: inline-block; padding: 7px 18px; border-radius: 6px; font-size: 13px; font-weight: 600; cursor: pointer; border: none; margin: 0 4px; }
        .btn-print { background: {{ $branding['primary_color'] ?? '#0ea5e9' }}; color: #fff; }
        .btn-back  { background: #f1f5f9; color: #374151; text-decoration: none; }
        @media print {
            .print-controls { display: none !important; }
            .page { padding: 15px; }
        }
    </style>
</head>
<body>
<div class="page">

    <div class="print-controls">
        @if($context === 'admin')
            <a href="javascript:history.back()" class="btn btn-back">← Back</a>
        @elseif($context === 'customer')
            <a href="{{ route('customer.payments.index') }}" class="btn btn-back">← Back</a>
        @elseif($context === 'dealer')
            <a href="{{ route('dealer.payments.index') }}" class="btn btn-back">← Back</a>
        @endif
        <button onclick="window.print()" class="btn btn-print">🖨 Print</button>
    </div>

    <div class="header">
        <div class="company-logo">
            @if($logoUrl)
                <img src="{{ $logoUrl }}" alt="{{ $company['name'] ?? 'Waterfall' }}" style="max-height:50px;max-width:140px;object-fit:contain;">
            @else
                <div class="text-logo">💧 {{ $company['name'] ?? 'Waterfall' }}</div>
            @endif
            @if($company['tagline'] ?? '') <div style="font-size:10px;color:#94a3b8;margin-top:2px;">{{ $company['tagline'] }}</div> @endif
        </div>
        <div class="company-info">
            <div class="company-name">{{ $company['name'] ?? 'Waterfall' }}</div>
            @if($company['address'] ?? '') <div>{{ $company['address'] }}</div> @endif
            @if($company['mobile'] ?? '') <div>📞 {{ $company['mobile'] }}</div> @endif
            @if($company['email'] ?? '') <div>✉ {{ $company['email'] }}</div> @endif
        </div>
    </div>

    <div class="doc-title"><h1>PAYMENT RECEIPT</h1></div>

    {{-- Amount highlight --}}
    <div class="amount-box">
        <div class="label">Amount Received</div>
        <div class="amount">{{ $billing['currency'] ?? 'BDT' }} {{ number_format((float)$payment->amount, 2) }}</div>
    </div>

    {{-- Payment Details --}}
    <div class="info-box">
        <h3>Payment Details</h3>
        <div class="info-row"><span class="info-label">Payment No</span><span class="info-value" style="font-family:monospace;">{{ $payment->payment_no }}</span></div>
        <div class="info-row"><span class="info-label">Payment Date</span><span class="info-value">{{ $payment->payment_date?->format('d M Y') }}</span></div>
        <div class="info-row"><span class="info-label">Payment Method</span><span class="info-value">{{ ucfirst($payment->payment_method) }}</span></div>
        @if($payment->reference_no)
        <div class="info-row"><span class="info-label">Reference No</span><span class="info-value">{{ $payment->reference_no }}</span></div>
        @endif
        @if($payment->receivedBy)
        <div class="info-row"><span class="info-label">Received By</span><span class="info-value">{{ $payment->receivedBy->name }}</span></div>
        @endif
    </div>

    {{-- Received From --}}
    <div class="info-box">
        <h3>Received From</h3>
        @php
            $party = $payment->payment_type === 'customer' ? $payment->customer : $payment->dealer;
            $code  = $payment->payment_type === 'customer' ? ($party?->customer_id ?? '') : ($party?->dealer_code ?? '');
        @endphp
        <div class="info-row"><span class="info-label">{{ ucfirst($payment->payment_type) }} ID</span><span class="info-value" style="font-family:monospace;">{{ $code }}</span></div>
        <div class="info-row"><span class="info-label">Name</span><span class="info-value">{{ $party?->name ?? '—' }}</span></div>
        <div class="info-row"><span class="info-label">Mobile</span><span class="info-value">{{ $party?->mobile ?? '—' }}</span></div>
    </div>

    {{-- Invoice Info --}}
    @if($payment->invoice)
    <div class="info-box">
        <h3>Against Invoice</h3>
        <div class="info-row"><span class="info-label">Invoice No</span><span class="info-value" style="font-family:monospace;">{{ $payment->invoice->invoice_no }}</span></div>
        <div class="info-row"><span class="info-label">Invoice Total</span><span class="info-value">{{ number_format((float)$payment->invoice->total_amount, 2) }}</span></div>
        <div class="info-row"><span class="info-label">Total Paid</span><span class="info-value" style="color:#10b981;">{{ number_format((float)$payment->invoice->paid_amount, 2) }}</span></div>
        <div class="info-row"><span class="info-label">Remaining Due</span><span class="info-value" style="{{ (float)$payment->invoice->due_amount > 0 ? 'color:#ef4444;' : 'color:#10b981;' }}">{{ number_format((float)$payment->invoice->due_amount, 2) }}</span></div>
    </div>
    @endif

    @if($payment->remarks)
    <div style="background:#f8fafc;border-radius:6px;padding:10px;margin-bottom:14px;font-size:12px;color:#475569;">
        <strong>Remarks:</strong> {{ $payment->remarks }}
    </div>
    @endif

    <div class="footer">
        @if($branding['receipt_footer_note'] ?? '')
            <div style="font-weight:600;color:#475569;margin-bottom:4px;">{{ $branding['receipt_footer_note'] }}</div>
        @endif
        <div>This receipt is system generated. — {{ $company['name'] ?? 'Waterfall' }}</div>
        @if($company['support_mobile'] ?? '') <div>Support: {{ $company['support_mobile'] }}</div> @endif
    </div>

</div>
</body>
</html>
