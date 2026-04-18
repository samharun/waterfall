@extends('dealer.layouts.app')
@section('title', 'My Profile')
@section('content')
<p class="page-title">My Profile</p>
<div class="card">
    @foreach([
        ['Dealer Code',     $dealer->dealer_code,                   true],
        ['Name',            $dealer->name,                          false],
        ['Mobile',          $dealer->mobile,                        false],
        ['Email',           $dealer->email ?? '—',                  false],
        ['Zone',            $dealer->zone?->name ?? '—',            false],
        ['Approval Status', ucfirst($dealer->approval_status),      false],
        ['Address',         $dealer->address,                       false],
        ['Opening Balance', '৳ '.number_format((float)$dealer->opening_balance, 2), false],
        ['Current Due',     '৳ '.number_format((float)$dealer->current_due, 2),     false],
    ] as [$label, $value, $mono])
    <div class="list-item">
        <span class="list-label">{{ $label }}</span>
        <span class="list-value" @if($mono) style="font-family:monospace;font-size:.85rem;" @endif>{{ $value }}</span>
    </div>
    @endforeach
</div>
@endsection
