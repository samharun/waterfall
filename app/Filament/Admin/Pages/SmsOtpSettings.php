<?php

namespace App\Filament\Admin\Pages;

use App\Models\AppSetting;
use App\Services\SmsService;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class SmsOtpSettings extends Page
{
    use \App\Filament\Admin\Traits\HasPagePermission;

    protected static string $accessPermission = 'settings.sms_otp.manage';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static string|\UnitEnum|null $navigationGroup = 'Administration';

    protected static ?string $navigationLabel = 'SMS / OTP Settings';

    protected static ?int $navigationSort = 10;

    protected string $view = 'filament.admin.pages.sms-otp-settings';

    // ── SMS fields ─────────────────────────────────────────────────
    public bool   $sms_enabled       = false;
    public string $sms_provider      = 'log';
    public string $sms_sender_id     = '';
    public string $sms_api_url       = '';
    public string $sms_method        = 'POST';
    public string $sms_api_key       = '';
    public string $sms_api_secret    = '';
    public string $sms_username      = '';
    public string $sms_password      = '';
    public string $sms_from          = '';
    public string $sms_mobile_param  = 'to';
    public string $sms_message_param = 'message';
    public string $sms_extra_params  = '';
    public int    $sms_timeout       = 10;

    // ── OTP fields ─────────────────────────────────────────────────
    public int    $otp_length           = 6;
    public int    $otp_expiry_minutes   = 5;
    public int    $otp_max_attempts     = 5;
    public int    $otp_max_resend       = 3;
    public int    $otp_resend_cooldown  = 60;
    public bool   $otp_show_in_local    = true;
    public string $otp_message_template = 'Your Waterfall verification code is {otp}. It will expire in {minutes} minutes.';

    // ── Test SMS fields ────────────────────────────────────────────
    public string $test_mobile  = '';
    public string $test_message = 'This is a test SMS from Waterfall admin panel.';

    public function mount(): void
    {
        // Load SMS settings
        $this->sms_enabled       = (bool)   AppSetting::getValue('sms', 'enabled', false);
        $this->sms_provider      = (string) AppSetting::getValue('sms', 'provider', 'log');
        $this->sms_sender_id     = (string) AppSetting::getValue('sms', 'sender_id', '');
        $this->sms_api_url       = (string) AppSetting::getValue('sms', 'api_url', '');
        $this->sms_method        = (string) AppSetting::getValue('sms', 'method', 'POST');
        $this->sms_mobile_param  = (string) AppSetting::getValue('sms', 'mobile_param', 'to');
        $this->sms_message_param = (string) AppSetting::getValue('sms', 'message_param', 'message');
        $this->sms_extra_params  = (string) AppSetting::getValue('sms', 'extra_params', '');
        $this->sms_timeout       = (int)    AppSetting::getValue('sms', 'timeout_seconds', 10);
        $this->sms_from          = (string) AppSetting::getValue('sms', 'from', '');
        // Encrypted fields: show blank (never pre-fill secrets)

        // Load OTP settings
        $this->otp_length           = (int)    AppSetting::getValue('otp', 'length', 6);
        $this->otp_expiry_minutes   = (int)    AppSetting::getValue('otp', 'expiry_minutes', 5);
        $this->otp_max_attempts     = (int)    AppSetting::getValue('otp', 'max_attempts', 5);
        $this->otp_max_resend       = (int)    AppSetting::getValue('otp', 'max_resend_count', 3);
        $this->otp_resend_cooldown  = (int)    AppSetting::getValue('otp', 'resend_cooldown_seconds', 60);
        $this->otp_show_in_local    = (bool)   AppSetting::getValue('otp', 'show_in_local', true);
        $this->otp_message_template = (string) AppSetting::getValue('otp', 'message_template',
            'Your Waterfall verification code is {otp}. It will expire in {minutes} minutes.');
    }

    public function saveSettings(): void
    {
        $this->validate([
            'sms_timeout'          => ['required', 'integer', 'min:1', 'max:60'],
            'otp_length'           => ['required', 'integer', 'min:4', 'max:8'],
            'otp_expiry_minutes'   => ['required', 'integer', 'min:1', 'max:30'],
            'otp_max_attempts'     => ['required', 'integer', 'min:1', 'max:10'],
            'otp_max_resend'       => ['required', 'integer', 'min:0', 'max:10'],
            'otp_resend_cooldown'  => ['required', 'integer', 'min:10', 'max:300'],
            'otp_message_template' => ['required', 'string'],
        ]);

        // SMS non-secret settings
        AppSetting::setValue('sms', 'enabled',       $this->sms_enabled ? '1' : '0', 'boolean');
        AppSetting::setValue('sms', 'provider',      $this->sms_provider);
        AppSetting::setValue('sms', 'sender_id',     $this->sms_sender_id);
        AppSetting::setValue('sms', 'api_url',       $this->sms_api_url);
        AppSetting::setValue('sms', 'method',        $this->sms_method);
        AppSetting::setValue('sms', 'mobile_param',  $this->sms_mobile_param);
        AppSetting::setValue('sms', 'message_param', $this->sms_message_param);
        AppSetting::setValue('sms', 'extra_params',  $this->sms_extra_params);
        AppSetting::setValue('sms', 'timeout_seconds', (string) $this->sms_timeout, 'integer');
        AppSetting::setValue('sms', 'from',          $this->sms_from);

        // Encrypted fields: only save if non-blank
        foreach ([
            'api_key'    => $this->sms_api_key,
            'api_secret' => $this->sms_api_secret,
            'username'   => $this->sms_username,
            'password'   => $this->sms_password,
        ] as $key => $value) {
            if ($value !== '') {
                AppSetting::setValue('sms', $key, $value, 'string', true);
            }
        }

        // OTP settings
        AppSetting::setValue('otp', 'length',                  (string) $this->otp_length, 'integer');
        AppSetting::setValue('otp', 'expiry_minutes',          (string) $this->otp_expiry_minutes, 'integer');
        AppSetting::setValue('otp', 'max_attempts',            (string) $this->otp_max_attempts, 'integer');
        AppSetting::setValue('otp', 'max_resend_count',        (string) $this->otp_max_resend, 'integer');
        AppSetting::setValue('otp', 'resend_cooldown_seconds', (string) $this->otp_resend_cooldown, 'integer');
        AppSetting::setValue('otp', 'show_in_local',           $this->otp_show_in_local ? '1' : '0', 'boolean');
        AppSetting::setValue('otp', 'message_template',        $this->otp_message_template, 'text');

        Notification::make()->title('Settings saved successfully')->success()->send();
    }

    public function sendTestSms(): void
    {
        $this->validate([
            'test_mobile'  => ['required', 'regex:/^01[3-9][0-9]{8}$/'],
            'test_message' => ['required', 'string', 'max:160'],
        ], [
            'test_mobile.regex' => 'Please enter a valid Bangladesh mobile number (01XXXXXXXXX).',
        ]);

        $result = app(SmsService::class)->send($this->test_mobile, $this->test_message);

        if ($result) {
            Notification::make()
                ->title('Test SMS sent (or logged)')
                ->body('Check storage/logs/laravel.log if using log provider.')
                ->success()
                ->send();
        } else {
            Notification::make()
                ->title('Test SMS failed')
                ->body('Check logs for details.')
                ->danger()
                ->send();
        }
    }
}

