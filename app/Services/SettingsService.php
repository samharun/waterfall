<?php

namespace App\Services;

use App\Models\AppSetting;

class SettingsService
{
    public function get(string $group, string $key, mixed $default = null): mixed
    {
        return AppSetting::getValue($group, $key, $default);
    }

    public function set(string $group, string $key, mixed $value, string $type = 'string', bool $encrypted = false): void
    {
        AppSetting::setValue($group, $key, $value, $type, $encrypted);
    }

    public function company(): array
    {
        return AppSetting::getGroup('company') + [
            'name'            => 'Waterfall',
            'legal_name'      => '',
            'tagline'         => 'Pure Drinking Water Delivery',
            'mobile'          => '',
            'phone'           => '',
            'email'           => '',
            'website'         => '',
            'address'         => '',
            'trade_license_no'=> '',
            'bin_no'          => '',
            'tin_no'          => '',
            'support_mobile'  => '',
            'support_email'   => '',
        ];
    }

    public function branding(): array
    {
        return AppSetting::getGroup('branding') + [
            'logo_path'           => '',
            'primary_color'       => '#0ea5e9',
            'secondary_color'     => '#0369a1',
            'invoice_footer_note' => 'Thank you for choosing Waterfall.',
            'receipt_footer_note' => 'This receipt is system generated.',
        ];
    }

    public function billing(): array
    {
        return AppSetting::getGroup('billing') + [
            'currency'       => 'BDT',
            'invoice_prefix' => 'WF-INV',
            'payment_prefix' => 'WF-PAY',
        ];
    }

    public function logoUrl(): ?string
    {
        $path = $this->get('branding', 'logo_path', '');
        if (! $path) {
            return null;
        }
        return asset('storage/' . $path);
    }
}
