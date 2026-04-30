<?php

namespace App\Filament\Admin\Pages;

use App\Models\AppSetting;
use App\Services\SettingsService;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Storage;

class CompanySettings extends Page
{
    use \App\Filament\Admin\Traits\HasPagePermission;

    protected static string $accessPermission = 'settings.company.manage';
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-building-office';

    protected static string|\UnitEnum|null $navigationGroup = 'Administration';

    protected static ?string $navigationLabel = 'Company Settings';

    protected static ?int $navigationSort = 9;

    protected string $view = 'filament.admin.pages.company-settings';

    // ── Company fields ─────────────────────────────────────────────
    public string $company_name            = '';
    public string $company_legal_name      = '';
    public string $company_tagline         = '';
    public string $company_mobile          = '';
    public string $company_phone           = '';
    public string $company_email           = '';
    public string $company_website         = '';
    public string $company_address         = '';
    public string $company_trade_license   = '';
    public string $company_bin_no          = '';
    public string $company_tin_no          = '';
    public string $company_support_mobile  = '';
    public string $company_support_email   = '';

    // ── Branding fields ────────────────────────────────────────────
    public string $branding_primary_color        = '#0ea5e9';
    public string $branding_secondary_color      = '#0369a1';
    public string $branding_invoice_footer_note  = '';
    public string $branding_receipt_footer_note  = '';

    // ── Billing fields ─────────────────────────────────────────────
    public string $billing_currency       = 'BDT';
    public string $billing_invoice_prefix = 'WF-INV';
    public string $billing_payment_prefix = 'WF-PAY';

    // ── Logo upload ────────────────────────────────────────────────
    public $logo_file = null;
    public string $current_logo_path = '';

    public function mount(): void
    {
        $settings = app(SettingsService::class);
        $company  = $settings->company();
        $branding = $settings->branding();
        $billing  = $settings->billing();

        $this->company_name           = (string) ($company['name'] ?? '');
        $this->company_legal_name     = (string) ($company['legal_name'] ?? '');
        $this->company_tagline        = (string) ($company['tagline'] ?? '');
        $this->company_mobile         = (string) ($company['mobile'] ?? '');
        $this->company_phone          = (string) ($company['phone'] ?? '');
        $this->company_email          = (string) ($company['email'] ?? '');
        $this->company_website        = (string) ($company['website'] ?? '');
        $this->company_address        = (string) ($company['address'] ?? '');
        $this->company_trade_license  = (string) ($company['trade_license_no'] ?? '');
        $this->company_bin_no         = (string) ($company['bin_no'] ?? '');
        $this->company_tin_no         = (string) ($company['tin_no'] ?? '');
        $this->company_support_mobile = (string) ($company['support_mobile'] ?? '');
        $this->company_support_email  = (string) ($company['support_email'] ?? '');

        $this->branding_primary_color       = (string) ($branding['primary_color'] ?? '#0ea5e9');
        $this->branding_secondary_color     = (string) ($branding['secondary_color'] ?? '#0369a1');
        $this->branding_invoice_footer_note = (string) ($branding['invoice_footer_note'] ?? '');
        $this->branding_receipt_footer_note = (string) ($branding['receipt_footer_note'] ?? '');
        $this->current_logo_path            = (string) ($branding['logo_path'] ?? '');

        $this->billing_currency       = (string) ($billing['currency'] ?? 'BDT');
        $this->billing_invoice_prefix = (string) ($billing['invoice_prefix'] ?? 'WF-INV');
        $this->billing_payment_prefix = (string) ($billing['payment_prefix'] ?? 'WF-PAY');
    }

    public function saveSettings(): void
    {
        $this->validate([
            'company_name'          => ['required', 'string', 'max:255'],
            'company_email'         => ['nullable', 'email', 'max:255'],
            'company_support_email' => ['nullable', 'email', 'max:255'],
            'company_website'       => ['nullable', 'url', 'max:255'],
            'company_mobile'        => ['nullable', 'max:30'],
            'company_phone'         => ['nullable', 'max:30'],
            'company_support_mobile'=> ['nullable', 'max:30'],
            'branding_primary_color'  => ['nullable', 'regex:/^#[0-9a-fA-F]{3,6}$/'],
            'branding_secondary_color'=> ['nullable', 'regex:/^#[0-9a-fA-F]{3,6}$/'],
            'logo_file'             => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        // Handle logo upload
        if ($this->logo_file) {
            // Delete old logo if exists
            if ($this->current_logo_path && Storage::disk('public')->exists($this->current_logo_path)) {
                Storage::disk('public')->delete($this->current_logo_path);
            }
            $path = $this->logo_file->store('company', 'public');
            $this->current_logo_path = $path;
            AppSetting::setValue('branding', 'logo_path', $path);
        }

        // Save company settings
        $map = [
            ['company', 'name',             $this->company_name],
            ['company', 'legal_name',        $this->company_legal_name],
            ['company', 'tagline',           $this->company_tagline],
            ['company', 'mobile',            $this->company_mobile],
            ['company', 'phone',             $this->company_phone],
            ['company', 'email',             $this->company_email],
            ['company', 'website',           $this->company_website],
            ['company', 'address',           $this->company_address, 'text'],
            ['company', 'trade_license_no',  $this->company_trade_license],
            ['company', 'bin_no',            $this->company_bin_no],
            ['company', 'tin_no',            $this->company_tin_no],
            ['company', 'support_mobile',    $this->company_support_mobile],
            ['company', 'support_email',     $this->company_support_email],
        ];

        foreach ($map as $item) {
            AppSetting::setValue($item[0], $item[1], $item[2], $item[3] ?? 'string');
        }

        // Save branding settings
        AppSetting::setValue('branding', 'primary_color',       $this->branding_primary_color);
        AppSetting::setValue('branding', 'secondary_color',     $this->branding_secondary_color);
        AppSetting::setValue('branding', 'invoice_footer_note', $this->branding_invoice_footer_note, 'text');
        AppSetting::setValue('branding', 'receipt_footer_note', $this->branding_receipt_footer_note, 'text');

        // Save billing settings
        AppSetting::setValue('billing', 'currency',        $this->billing_currency);
        AppSetting::setValue('billing', 'invoice_prefix',  $this->billing_invoice_prefix);
        AppSetting::setValue('billing', 'payment_prefix',  $this->billing_payment_prefix);

        $this->logo_file = null;

        Notification::make()->title('Company settings saved')->success()->send();
    }
}

