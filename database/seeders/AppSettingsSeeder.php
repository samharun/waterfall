<?php

namespace Database\Seeders;

use App\Models\AppSetting;
use Illuminate\Database\Seeder;

class AppSettingsSeeder extends Seeder
{
    public function run(): void
    {
        // ── SMS defaults ───────────────────────────────────────────
        $smsDefaults = [
            ['key' => 'enabled',       'value' => '0',     'type' => 'boolean', 'encrypted' => false, 'desc' => 'Enable real SMS sending'],
            ['key' => 'provider',      'value' => 'log',   'type' => 'string',  'encrypted' => false, 'desc' => 'SMS provider: log|generic_http|ssl_wireless|bulk_sms_bd|custom'],
            ['key' => 'sender_id',     'value' => '',      'type' => 'string',  'encrypted' => false, 'desc' => 'Sender ID / SID'],
            ['key' => 'api_url',       'value' => '',      'type' => 'string',  'encrypted' => false, 'desc' => 'API endpoint URL'],
            ['key' => 'method',        'value' => 'POST',  'type' => 'string',  'encrypted' => false, 'desc' => 'HTTP method: GET or POST'],
            ['key' => 'mobile_param',  'value' => 'to',    'type' => 'string',  'encrypted' => false, 'desc' => 'Mobile number parameter name'],
            ['key' => 'message_param', 'value' => 'message','type'=> 'string',  'encrypted' => false, 'desc' => 'Message parameter name'],
            ['key' => 'from',          'value' => '',      'type' => 'string',  'encrypted' => false, 'desc' => 'From/sender name'],
            ['key' => 'extra_params',  'value' => '',      'type' => 'string',  'encrypted' => false, 'desc' => 'Extra params as JSON'],
            ['key' => 'timeout_seconds','value'=> '10',    'type' => 'integer', 'encrypted' => false, 'desc' => 'HTTP timeout in seconds'],
            // Encrypted — only create if not already set
            ['key' => 'api_key',       'value' => '',      'type' => 'string',  'encrypted' => true,  'desc' => 'API Key (encrypted)'],
            ['key' => 'api_secret',    'value' => '',      'type' => 'string',  'encrypted' => true,  'desc' => 'API Secret (encrypted)'],
            ['key' => 'username',      'value' => '',      'type' => 'string',  'encrypted' => true,  'desc' => 'Username (encrypted)'],
            ['key' => 'password',      'value' => '',      'type' => 'string',  'encrypted' => true,  'desc' => 'Password (encrypted)'],
        ];

        foreach ($smsDefaults as $s) {
            // For encrypted fields, only create if not already present (don't overwrite)
            if ($s['encrypted']) {
                AppSetting::firstOrCreate(
                    ['group' => 'sms', 'key' => $s['key']],
                    ['value' => '', 'type' => $s['type'], 'is_encrypted' => true, 'description' => $s['desc']]
                );
            } else {
                AppSetting::updateOrCreate(
                    ['group' => 'sms', 'key' => $s['key']],
                    ['value' => $s['value'], 'type' => $s['type'], 'is_encrypted' => false, 'description' => $s['desc']]
                );
            }
        }

        // ── OTP defaults ───────────────────────────────────────────
        $otpDefaults = [
            ['key' => 'length',                  'value' => '6',   'type' => 'integer', 'desc' => 'OTP digit length (4-8)'],
            ['key' => 'expiry_minutes',          'value' => '5',   'type' => 'integer', 'desc' => 'OTP expiry in minutes'],
            ['key' => 'max_attempts',            'value' => '5',   'type' => 'integer', 'desc' => 'Max wrong OTP attempts'],
            ['key' => 'max_resend_count',        'value' => '3',   'type' => 'integer', 'desc' => 'Max OTP resend count'],
            ['key' => 'resend_cooldown_seconds', 'value' => '60',  'type' => 'integer', 'desc' => 'Seconds between resends'],
            ['key' => 'show_in_local',           'value' => '1',   'type' => 'boolean', 'desc' => 'Log OTP in local environment'],
            ['key' => 'message_template',        'value' => 'Your Waterfall verification code is {otp}. It will expire in {minutes} minutes.', 'type' => 'text', 'desc' => 'OTP SMS template'],
        ];

        foreach ($otpDefaults as $s) {
            AppSetting::updateOrCreate(
                ['group' => 'otp', 'key' => $s['key']],
                ['value' => $s['value'], 'type' => $s['type'], 'is_encrypted' => false, 'description' => $s['desc']]
            );
        }

        echo "AppSettings seeded.\n";

        $this->seedCompanyAndBranding();
    }

    private function seedCompanyAndBranding(): void
    {
        $companyDefaults = [
            ['key' => 'name',            'value' => 'Waterfall',                    'type' => 'string', 'desc' => 'Company name'],
            ['key' => 'legal_name',      'value' => '',                             'type' => 'string', 'desc' => 'Legal/registered name'],
            ['key' => 'tagline',         'value' => 'Pure Drinking Water Delivery', 'type' => 'string', 'desc' => 'Company tagline'],
            ['key' => 'mobile',          'value' => '',                             'type' => 'string', 'desc' => 'Primary mobile'],
            ['key' => 'phone',           'value' => '',                             'type' => 'string', 'desc' => 'Office phone'],
            ['key' => 'email',           'value' => '',                             'type' => 'string', 'desc' => 'Company email'],
            ['key' => 'website',         'value' => '',                             'type' => 'string', 'desc' => 'Website URL'],
            ['key' => 'address',         'value' => '',                             'type' => 'text',   'desc' => 'Company address'],
            ['key' => 'trade_license_no','value' => '',                             'type' => 'string', 'desc' => 'Trade license number'],
            ['key' => 'bin_no',          'value' => '',                             'type' => 'string', 'desc' => 'BIN number'],
            ['key' => 'tin_no',          'value' => '',                             'type' => 'string', 'desc' => 'TIN number'],
            ['key' => 'support_mobile',  'value' => '',                             'type' => 'string', 'desc' => 'Support mobile'],
            ['key' => 'support_email',   'value' => '',                             'type' => 'string', 'desc' => 'Support email'],
        ];

        foreach ($companyDefaults as $s) {
            AppSetting::updateOrCreate(
                ['group' => 'company', 'key' => $s['key']],
                ['value' => $s['value'], 'type' => $s['type'], 'is_encrypted' => false, 'description' => $s['desc']]
            );
        }

        $brandingDefaults = [
            ['key' => 'logo_path',            'value' => '',                                    'type' => 'string', 'desc' => 'Logo file path'],
            ['key' => 'primary_color',        'value' => '#0ea5e9',                             'type' => 'string', 'desc' => 'Primary brand color'],
            ['key' => 'secondary_color',      'value' => '#0369a1',                             'type' => 'string', 'desc' => 'Secondary brand color'],
            ['key' => 'invoice_footer_note',  'value' => 'Thank you for choosing Waterfall.',   'type' => 'text',   'desc' => 'Invoice footer note'],
            ['key' => 'receipt_footer_note',  'value' => 'This receipt is system generated.',   'type' => 'text',   'desc' => 'Receipt footer note'],
        ];

        foreach ($brandingDefaults as $s) {
            AppSetting::updateOrCreate(
                ['group' => 'branding', 'key' => $s['key']],
                ['value' => $s['value'], 'type' => $s['type'], 'is_encrypted' => false, 'description' => $s['desc']]
            );
        }

        $billingDefaults = [
            ['key' => 'currency',        'value' => 'BDT',    'type' => 'string', 'desc' => 'Currency code'],
            ['key' => 'invoice_prefix',  'value' => 'WF-INV', 'type' => 'string', 'desc' => 'Invoice number prefix (future use)'],
            ['key' => 'payment_prefix',  'value' => 'WF-PAY', 'type' => 'string', 'desc' => 'Payment number prefix (future use)'],
        ];

        foreach ($billingDefaults as $s) {
            AppSetting::updateOrCreate(
                ['group' => 'billing', 'key' => $s['key']],
                ['value' => $s['value'], 'type' => $s['type'], 'is_encrypted' => false, 'description' => $s['desc']]
            );
        }
    }
}
