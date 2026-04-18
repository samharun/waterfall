<?php

namespace App\Services;

use App\Models\AppSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsService
{
    /**
     * Send an SMS. Reads provider config from app_settings (group=sms).
     */
    public function send(string $mobile, string $message): bool
    {
        $enabled  = (bool) AppSetting::getValue('sms', 'enabled', false);
        $provider = AppSetting::getValue('sms', 'provider', 'log');

        // Always log for debugging (no secrets logged)
        Log::info("[SMS] To: {$mobile} | Provider: {$provider} | Enabled: " . ($enabled ? 'yes' : 'no'));

        if (! $enabled || $provider === 'log') {
            return true; // logged above
        }

        return match ($provider) {
            'generic_http'  => $this->sendViaGenericHttp($mobile, $message),
            'ssl_wireless'  => $this->sendViaSslWireless($mobile, $message),
            'bulk_sms_bd'   => $this->sendViaBulkSmsBd($mobile, $message),
            default         => $this->logOnly($mobile, $message),
        };
    }

    // ── Generic HTTP (configurable) ────────────────────────────────

    protected function sendViaGenericHttp(string $mobile, string $message): bool
    {
        $url       = AppSetting::getValue('sms', 'api_url', '');
        $method    = strtoupper(AppSetting::getValue('sms', 'method', 'POST'));
        $apiKey    = AppSetting::getValue('sms', 'api_key', '');
        $apiSecret = AppSetting::getValue('sms', 'api_secret', '');
        $username  = AppSetting::getValue('sms', 'username', '');
        $password  = AppSetting::getValue('sms', 'password', '');
        $from      = AppSetting::getValue('sms', 'sender_id', '');
        $mobileParam  = AppSetting::getValue('sms', 'mobile_param', 'to');
        $messageParam = AppSetting::getValue('sms', 'message_param', 'message');
        $extraParams  = AppSetting::getValue('sms', 'extra_params', []);
        $timeout      = (int) AppSetting::getValue('sms', 'timeout_seconds', 10);

        if (! $url) {
            Log::warning('[SMS] Generic HTTP provider: API URL not configured.');
            return false;
        }

        $params = array_merge(
            is_array($extraParams) ? $extraParams : [],
            [
                $mobileParam  => $mobile,
                $messageParam => $message,
            ]
        );

        if ($apiKey)    $params['api_key']    = $apiKey;
        if ($apiSecret) $params['api_secret'] = $apiSecret;
        if ($username)  $params['username']   = $username;
        if ($password)  $params['password']   = $password;
        if ($from)      $params['from']       = $from;

        try {
            $http = Http::timeout($timeout);

            $response = $method === 'GET'
                ? $http->get($url, $params)
                : $http->asForm()->post($url, $params);

            if ($response->successful()) {
                Log::info("[SMS] Generic HTTP sent to {$mobile}. Status: {$response->status()}");
                return true;
            }

            Log::warning("[SMS] Generic HTTP failed for {$mobile}. Status: {$response->status()}");
            return false;

        } catch (\Throwable $e) {
            Log::error("[SMS] Generic HTTP exception for {$mobile}: " . $e->getMessage());
            return false;
        }
    }

    // ── SSL Wireless (Bangladesh) ──────────────────────────────────

    protected function sendViaSslWireless(string $mobile, string $message): bool
    {
        $url      = AppSetting::getValue('sms', 'api_url', 'https://sms.sslwireless.com/pushapi/dynamic/server.php');
        $apiToken = AppSetting::getValue('sms', 'api_key', '');
        $sid      = AppSetting::getValue('sms', 'sender_id', '');
        $timeout  = (int) AppSetting::getValue('sms', 'timeout_seconds', 10);

        try {
            $response = Http::timeout($timeout)->post($url, [
                'api_token' => $apiToken,
                'sid'       => $sid,
                'msisdn'    => $mobile,
                'sms'       => $message,
                'csms_id'   => uniqid('WF_'),
            ]);

            $body = $response->json();

            if (isset($body['status_code']) && $body['status_code'] == 200) {
                Log::info("[SMS] SSL Wireless sent to {$mobile}");
                return true;
            }

            Log::warning("[SMS] SSL Wireless failed for {$mobile}: " . ($body['status_text'] ?? 'unknown'));
            return false;

        } catch (\Throwable $e) {
            Log::error("[SMS] SSL Wireless exception for {$mobile}: " . $e->getMessage());
            return false;
        }
    }

    // ── Bulk SMS BD (placeholder) ──────────────────────────────────

    protected function sendViaBulkSmsBd(string $mobile, string $message): bool
    {
        // TODO: Implement Bulk SMS BD provider
        Log::info("[SMS] Bulk SMS BD placeholder for {$mobile}");
        return $this->sendViaGenericHttp($mobile, $message);
    }

    // ── Log only ──────────────────────────────────────────────────

    protected function logOnly(string $mobile, string $message): bool
    {
        Log::info("[SMS] Log-only provider. To: {$mobile}");
        return true;
    }
}
