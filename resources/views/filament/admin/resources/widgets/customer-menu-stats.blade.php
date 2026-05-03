@php
    $tones = [
        'blue' => 'background:linear-gradient(135deg,#0077B6,#005f92);box-shadow:0 2px 8px rgba(0,119,182,.2);color:#fff',
        'green' => 'background:linear-gradient(135deg,#16a34a,#15803d);box-shadow:0 2px 8px rgba(22,163,74,.2);color:#fff',
        'amber' => 'background:linear-gradient(135deg,#f59e0b,#d97706);box-shadow:0 2px 8px rgba(245,158,11,.2);color:#fff',
        'white' => 'background:#fff;border:1px solid #e5e7eb;box-shadow:0 1px 3px rgba(0,0,0,.06);color:#0077B6',
    ];
@endphp

<div style="display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px;margin-bottom:4px">
    @foreach($this->getCards() as $card)
        @php
            $tone = $card['tone'] ?? 'blue';
            $isWhite = $tone === 'white';
        @endphp
        <div style="{{ $tones[$tone] ?? $tones['blue'] }};border-radius:12px;padding:16px;min-width:0">
            <div style="font-size:11px;font-weight:700;color:{{ $isWhite ? '#6b7280' : 'rgba(255,255,255,.75)' }};text-transform:uppercase;letter-spacing:.06em;margin-bottom:6px">
                {{ $card['label'] }}
            </div>
            <div style="font-size:{{ strlen($card['value']) > 12 ? '20px' : '30px' }};font-weight:800;color:{{ $isWhite ? '#0077B6' : '#fff' }};line-height:1.15;overflow-wrap:anywhere">
                {{ $card['value'] }}
            </div>
            @if(! empty($card['hint']))
                <div style="font-size:11px;color:{{ $isWhite ? '#dc2626' : 'rgba(255,255,255,.82)' }};margin-top:4px">
                    {{ $card['hint'] }}
                </div>
            @endif
        </div>
    @endforeach
</div>
