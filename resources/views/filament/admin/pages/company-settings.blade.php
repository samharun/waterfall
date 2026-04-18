<x-filament-panels::page>
<div class="space-y-6">

    {{-- Company Information --}}
    <x-filament::section>
        <x-slot name="heading">Company Information</x-slot>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div class="sm:col-span-2">
                <label class="block text-sm font-semibold mb-1">Company Name *</label>
                <input type="text" wire:model="company_name" class="w-full border rounded-lg px-3 py-2 text-sm" required>
                @error('company_name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1">Legal Name</label>
                <input type="text" wire:model="company_legal_name" class="w-full border rounded-lg px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1">Tagline</label>
                <input type="text" wire:model="company_tagline" class="w-full border rounded-lg px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1">Mobile</label>
                <input type="text" wire:model="company_mobile" class="w-full border rounded-lg px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1">Phone</label>
                <input type="text" wire:model="company_phone" class="w-full border rounded-lg px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1">Email</label>
                <input type="email" wire:model="company_email" class="w-full border rounded-lg px-3 py-2 text-sm">
                @error('company_email')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1">Website</label>
                <input type="url" wire:model="company_website" class="w-full border rounded-lg px-3 py-2 text-sm" placeholder="https://...">
                @error('company_website')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div class="sm:col-span-2">
                <label class="block text-sm font-semibold mb-1">Address</label>
                <textarea wire:model="company_address" rows="3" class="w-full border rounded-lg px-3 py-2 text-sm"></textarea>
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1">Trade License No</label>
                <input type="text" wire:model="company_trade_license" class="w-full border rounded-lg px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1">BIN No</label>
                <input type="text" wire:model="company_bin_no" class="w-full border rounded-lg px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1">TIN No</label>
                <input type="text" wire:model="company_tin_no" class="w-full border rounded-lg px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1">Support Mobile</label>
                <input type="text" wire:model="company_support_mobile" class="w-full border rounded-lg px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1">Support Email</label>
                <input type="email" wire:model="company_support_email" class="w-full border rounded-lg px-3 py-2 text-sm">
            </div>
        </div>
    </x-filament::section>

    {{-- Branding --}}
    <x-filament::section>
        <x-slot name="heading">Branding</x-slot>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div class="sm:col-span-2">
                <label class="block text-sm font-semibold mb-1">Company Logo</label>
                @if($current_logo_path)
                    <div class="mb-2">
                        <img src="{{ asset('storage/' . $current_logo_path) }}" alt="Logo" class="h-16 object-contain border rounded p-1">
                        <p class="text-xs text-gray-400 mt-1">Current logo. Upload new to replace.</p>
                    </div>
                @endif
                <input type="file" wire:model="logo_file" accept="image/jpg,image/jpeg,image/png,image/webp"
                    class="w-full border rounded-lg px-3 py-2 text-sm">
                @error('logo_file')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                <p class="text-xs text-gray-400 mt-1">Max 2MB. JPG, PNG, WebP accepted.</p>
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1">Primary Color</label>
                <div class="flex gap-2 items-center">
                    <input type="color" wire:model="branding_primary_color" class="h-9 w-12 border rounded cursor-pointer">
                    <input type="text" wire:model="branding_primary_color" class="flex-1 border rounded-lg px-3 py-2 text-sm font-mono" placeholder="#0ea5e9">
                </div>
                @error('branding_primary_color')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1">Secondary Color</label>
                <div class="flex gap-2 items-center">
                    <input type="color" wire:model="branding_secondary_color" class="h-9 w-12 border rounded cursor-pointer">
                    <input type="text" wire:model="branding_secondary_color" class="flex-1 border rounded-lg px-3 py-2 text-sm font-mono" placeholder="#0369a1">
                </div>
            </div>
            <div class="sm:col-span-2">
                <label class="block text-sm font-semibold mb-1">Invoice Footer Note</label>
                <textarea wire:model="branding_invoice_footer_note" rows="2" class="w-full border rounded-lg px-3 py-2 text-sm"></textarea>
            </div>
            <div class="sm:col-span-2">
                <label class="block text-sm font-semibold mb-1">Receipt Footer Note</label>
                <textarea wire:model="branding_receipt_footer_note" rows="2" class="w-full border rounded-lg px-3 py-2 text-sm"></textarea>
            </div>
        </div>
    </x-filament::section>

    {{-- Billing Display --}}
    <x-filament::section>
        <x-slot name="heading">Billing Display</x-slot>
        <p class="text-xs text-amber-600 mb-3">⚠ Prefix settings are for future use and do not affect existing invoice/payment numbering.</p>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div>
                <label class="block text-sm font-semibold mb-1">Currency</label>
                <input type="text" wire:model="billing_currency" class="w-full border rounded-lg px-3 py-2 text-sm" placeholder="BDT">
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1">Invoice Prefix</label>
                <input type="text" wire:model="billing_invoice_prefix" class="w-full border rounded-lg px-3 py-2 text-sm font-mono">
            </div>
            <div>
                <label class="block text-sm font-semibold mb-1">Payment Prefix</label>
                <input type="text" wire:model="billing_payment_prefix" class="w-full border rounded-lg px-3 py-2 text-sm font-mono">
            </div>
        </div>
    </x-filament::section>

    <div class="flex justify-end">
        <x-filament::button wire:click="saveSettings" size="lg" icon="heroicon-o-check">
            Save Settings
        </x-filament::button>
    </div>
</div>
</x-filament-panels::page>
