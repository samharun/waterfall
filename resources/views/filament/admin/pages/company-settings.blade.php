<x-filament-panels::page>

@php
    $inputStyle  = "width:100%;padding:8px 12px;border:1px solid #d1d5db;border-radius:8px;font-size:13px;color:#111827;background:#f9fafb;outline:none;box-sizing:border-box";
    $labelStyle  = "display:block;font-size:12px;font-weight:600;color:#374151;margin-bottom:5px";
    $sectionStyle = "background:#fff;border:1px solid #e5e7eb;border-radius:12px;overflow:hidden;margin-bottom:20px;box-shadow:0 1px 3px rgba(0,0,0,.06)";
    $sectionHead  = "padding:14px 20px;border-bottom:1px solid #f3f4f6;background:#f9fafb;font-size:14px;font-weight:700;color:#111827;display:flex;align-items:center;gap:8px";
    $gridStyle    = "display:grid;grid-template-columns:repeat(2,1fr);gap:16px;padding:20px";
    $errorStyle   = "font-size:11px;color:#dc2626;margin-top:4px";
@endphp

{{-- Company Information --}}
<div style="{{ $sectionStyle }}">
    <div style="{{ $sectionHead }}">
        <svg width="16" height="16" fill="none" stroke="#0077B6" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21"/>
        </svg>
        Company Information
    </div>
    <div style="{{ $gridStyle }}">
        <div style="grid-column:span 2">
            <label style="{{ $labelStyle }}">Company Name *</label>
            <input type="text" wire:model="company_name" style="{{ $inputStyle }}">
            @error('company_name')<p style="{{ $errorStyle }}">{{ $message }}</p>@enderror
        </div>
        <div>
            <label style="{{ $labelStyle }}">Legal Name</label>
            <input type="text" wire:model="company_legal_name" style="{{ $inputStyle }}">
        </div>
        <div>
            <label style="{{ $labelStyle }}">Tagline</label>
            <input type="text" wire:model="company_tagline" style="{{ $inputStyle }}">
        </div>
        <div>
            <label style="{{ $labelStyle }}">Mobile</label>
            <input type="text" wire:model="company_mobile" style="{{ $inputStyle }}">
        </div>
        <div>
            <label style="{{ $labelStyle }}">Phone</label>
            <input type="text" wire:model="company_phone" style="{{ $inputStyle }}">
        </div>
        <div>
            <label style="{{ $labelStyle }}">Email</label>
            <input type="email" wire:model="company_email" style="{{ $inputStyle }}">
            @error('company_email')<p style="{{ $errorStyle }}">{{ $message }}</p>@enderror
        </div>
        <div>
            <label style="{{ $labelStyle }}">Website</label>
            <input type="url" wire:model="company_website" placeholder="https://..." style="{{ $inputStyle }}">
            @error('company_website')<p style="{{ $errorStyle }}">{{ $message }}</p>@enderror
        </div>
        <div style="grid-column:span 2">
            <label style="{{ $labelStyle }}">Address</label>
            <textarea wire:model="company_address" rows="3" style="{{ $inputStyle }}"></textarea>
        </div>
        <div>
            <label style="{{ $labelStyle }}">Trade License No</label>
            <input type="text" wire:model="company_trade_license" style="{{ $inputStyle }}">
        </div>
        <div>
            <label style="{{ $labelStyle }}">BIN No</label>
            <input type="text" wire:model="company_bin_no" style="{{ $inputStyle }}">
        </div>
        <div>
            <label style="{{ $labelStyle }}">TIN No</label>
            <input type="text" wire:model="company_tin_no" style="{{ $inputStyle }}">
        </div>
        <div>
            <label style="{{ $labelStyle }}">Support Mobile</label>
            <input type="text" wire:model="company_support_mobile" style="{{ $inputStyle }}">
        </div>
        <div style="grid-column:span 2">
            <label style="{{ $labelStyle }}">Support Email</label>
            <input type="email" wire:model="company_support_email" style="{{ $inputStyle }}">
        </div>
    </div>
</div>

{{-- Branding --}}
<div style="{{ $sectionStyle }}">
    <div style="{{ $sectionHead }}">
        <svg width="16" height="16" fill="none" stroke="#7c3aed" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9.53 16.122a3 3 0 0 0-5.78 1.128 2.25 2.25 0 0 1-2.4 2.245 4.5 4.5 0 0 0 8.4-2.245c0-.399-.078-.78-.22-1.128Zm0 0a15.998 15.998 0 0 0 3.388-1.62m-5.043-.025a15.994 15.994 0 0 1 1.622-3.395m3.42 3.42a15.995 15.995 0 0 0 4.764-4.648l3.876-5.814a1.151 1.151 0 0 0-1.597-1.597L14.146 6.32a15.996 15.996 0 0 0-4.649 4.763m3.42 3.42a6.776 6.776 0 0 0-3.42-3.42"/>
        </svg>
        Branding
    </div>
    <div style="{{ $gridStyle }}">
        <div style="grid-column:span 2">
            <label style="{{ $labelStyle }}">Company Logo</label>
            @if($current_logo_path)
                <div style="margin-bottom:10px;display:flex;align-items:center;gap:12px">
                    <img src="{{ asset('storage/' . $current_logo_path) }}" alt="Logo"
                         style="height:52px;object-fit:contain;border:1px solid #e5e7eb;border-radius:8px;padding:6px;background:#f9fafb">
                    <span style="font-size:12px;color:#9ca3af">Current logo. Upload new to replace.</span>
                </div>
            @endif
            <input type="file" wire:model="logo_file" accept="image/jpg,image/jpeg,image/png,image/webp"
                   style="{{ $inputStyle }}">
            @error('logo_file')<p style="{{ $errorStyle }}">{{ $message }}</p>@enderror
            <p style="font-size:11px;color:#9ca3af;margin-top:4px">Max 2MB. JPG, PNG, WebP accepted.</p>
        </div>

        <div>
            <label style="{{ $labelStyle }}">Primary Color</label>
            <div style="display:flex;gap:8px;align-items:center">
                <input type="color" wire:model="branding_primary_color"
                       style="height:38px;width:48px;border:1px solid #d1d5db;border-radius:6px;cursor:pointer;padding:2px">
                <input type="text" wire:model="branding_primary_color" placeholder="#0ea5e9"
                       style="{{ $inputStyle }};font-family:monospace">
            </div>
            @error('branding_primary_color')<p style="{{ $errorStyle }}">{{ $message }}</p>@enderror
        </div>

        <div>
            <label style="{{ $labelStyle }}">Secondary Color</label>
            <div style="display:flex;gap:8px;align-items:center">
                <input type="color" wire:model="branding_secondary_color"
                       style="height:38px;width:48px;border:1px solid #d1d5db;border-radius:6px;cursor:pointer;padding:2px">
                <input type="text" wire:model="branding_secondary_color" placeholder="#0369a1"
                       style="{{ $inputStyle }};font-family:monospace">
            </div>
        </div>

        <div style="grid-column:span 2">
            <label style="{{ $labelStyle }}">Invoice Footer Note</label>
            <textarea wire:model="branding_invoice_footer_note" rows="2" style="{{ $inputStyle }}"></textarea>
        </div>
        <div style="grid-column:span 2">
            <label style="{{ $labelStyle }}">Receipt Footer Note</label>
            <textarea wire:model="branding_receipt_footer_note" rows="2" style="{{ $inputStyle }}"></textarea>
        </div>
    </div>
</div>

{{-- Billing Display --}}
<div style="{{ $sectionStyle }}">
    <div style="{{ $sectionHead }}">
        <svg width="16" height="16" fill="none" stroke="#059669" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75"/>
        </svg>
        Billing Display
    </div>
    <div style="padding:12px 20px">
        <div style="display:flex;align-items:center;gap:8px;padding:10px 14px;background:#fffbeb;border:1px solid #fde68a;border-radius:8px;margin-bottom:16px">
            <svg width="14" height="14" fill="none" stroke="#d97706" stroke-width="2" viewBox="0 0 24 24" style="flex-shrink:0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/>
            </svg>
            <span style="font-size:12px;color:#92400e">Prefix settings are for future use and do not affect existing invoice/payment numbering.</span>
        </div>
    </div>
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;padding:0 20px 20px">
        <div>
            <label style="{{ $labelStyle }}">Currency</label>
            <input type="text" wire:model="billing_currency" placeholder="BDT" style="{{ $inputStyle }}">
        </div>
        <div>
            <label style="{{ $labelStyle }}">Invoice Prefix</label>
            <input type="text" wire:model="billing_invoice_prefix" style="{{ $inputStyle }};font-family:monospace">
        </div>
        <div>
            <label style="{{ $labelStyle }}">Payment Prefix</label>
            <input type="text" wire:model="billing_payment_prefix" style="{{ $inputStyle }};font-family:monospace">
        </div>
    </div>
</div>

{{-- Save Button --}}
<div style="display:flex;justify-content:flex-end;padding-top:4px">
    <button wire:click="saveSettings"
            style="display:inline-flex;align-items:center;gap:8px;padding:10px 24px;background:#0077B6;color:#fff;border:none;border-radius:10px;font-size:14px;font-weight:700;cursor:pointer;box-shadow:0 2px 6px rgba(0,119,182,.3)">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
        </svg>
        Save Settings
    </button>
</div>

</x-filament-panels::page>
