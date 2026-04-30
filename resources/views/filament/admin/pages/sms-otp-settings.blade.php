<x-filament-panels::page>

@php
    $inputStyle   = "width:100%;padding:8px 12px;border:1px solid #d1d5db;border-radius:8px;font-size:13px;color:#111827;background:#f9fafb;outline:none;box-sizing:border-box";
    $selectStyle  = "width:100%;padding:8px 12px;border:1px solid #d1d5db;border-radius:8px;font-size:13px;color:#111827;background:#f9fafb;outline:none";
    $labelStyle   = "display:block;font-size:12px;font-weight:600;color:#374151;margin-bottom:5px";
    $sectionStyle = "background:#fff;border:1px solid #e5e7eb;border-radius:12px;overflow:hidden;margin-bottom:20px;box-shadow:0 1px 3px rgba(0,0,0,.06)";
    $sectionHead  = "padding:14px 20px;border-bottom:1px solid #f3f4f6;background:#f9fafb;display:flex;align-items:flex-start;justify-content:space-between;gap:12px";
    $errorStyle   = "font-size:11px;color:#dc2626;margin-top:4px";
@endphp

{{-- SMS Gateway --}}
<div style="{{ $sectionStyle }}">
    <div style="{{ $sectionHead }}">
        <div>
            <div style="font-size:14px;font-weight:700;color:#111827;display:flex;align-items:center;gap:8px">
                <svg width="16" height="16" fill="none" stroke="#0077B6" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 1.5H8.25A2.25 2.25 0 0 0 6 3.75v16.5a2.25 2.25 0 0 0 2.25 2.25h7.5A2.25 2.25 0 0 0 18 20.25V3.75a2.25 2.25 0 0 0-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 8.25h3m-3 3h3m-3 3h3"/>
                </svg>
                SMS Gateway
            </div>
            <div style="font-size:12px;color:#9ca3af;margin-top:3px">Configure the SMS provider used for OTP and notifications.</div>
        </div>
    </div>
    <div style="padding:20px;display:grid;grid-template-columns:repeat(2,1fr);gap:16px">

        {{-- Enable toggle --}}
        <div style="grid-column:span 2">
            <label style="display:flex;align-items:center;gap:10px;cursor:pointer;padding:12px 16px;background:#f9fafb;border:1px solid #e5e7eb;border-radius:8px">
                <input type="checkbox" wire:model="sms_enabled" style="width:16px;height:16px;cursor:pointer">
                <div>
                    <div style="font-size:13px;font-weight:600;color:#111827">Enable SMS Sending</div>
                    <div style="font-size:11px;color:#9ca3af;margin-top:1px">When disabled, SMS is only logged to storage/logs/laravel.log</div>
                </div>
            </label>
        </div>

        <div>
            <label style="{{ $labelStyle }}">Provider</label>
            <select wire:model="sms_provider" style="{{ $selectStyle }}">
                <option value="log">Log (Local Dev)</option>
                <option value="generic_http">Generic HTTP</option>
                <option value="ssl_wireless">SSL Wireless (BD)</option>
                <option value="bulk_sms_bd">Bulk SMS BD</option>
                <option value="custom">Custom</option>
            </select>
        </div>

        <div>
            <label style="{{ $labelStyle }}">Sender ID / SID</label>
            <input type="text" wire:model="sms_sender_id" placeholder="WATERFALL" style="{{ $inputStyle }}">
        </div>

        <div style="grid-column:span 2">
            <label style="{{ $labelStyle }}">API URL</label>
            <input type="url" wire:model="sms_api_url" placeholder="https://api.provider.com/send" style="{{ $inputStyle }}">
        </div>

        <div>
            <label style="{{ $labelStyle }}">HTTP Method</label>
            <select wire:model="sms_method" style="{{ $selectStyle }}">
                <option value="POST">POST</option>
                <option value="GET">GET</option>
            </select>
        </div>

        <div>
            <label style="{{ $labelStyle }}">Timeout (seconds)</label>
            <input type="number" wire:model="sms_timeout" min="1" max="60" style="{{ $inputStyle }}">
        </div>

        <div>
            <label style="{{ $labelStyle }}">Mobile Number Param</label>
            <input type="text" wire:model="sms_mobile_param" placeholder="to" style="{{ $inputStyle }}">
        </div>

        <div>
            <label style="{{ $labelStyle }}">Message Param</label>
            <input type="text" wire:model="sms_message_param" placeholder="message" style="{{ $inputStyle }}">
        </div>

        <div>
            <label style="{{ $labelStyle }}">From</label>
            <input type="text" wire:model="sms_from" placeholder="Sender name or number" style="{{ $inputStyle }}">
        </div>

        <div>
            <label style="{{ $labelStyle }}">Extra Params (JSON)</label>
            <input type="text" wire:model="sms_extra_params" placeholder='{"key":"value"}' style="{{ $inputStyle }};font-family:monospace">
        </div>

        {{-- Encrypted fields --}}
        <div style="grid-column:span 2;border-top:1px solid #f3f4f6;padding-top:16px;margin-top:4px">
            <div style="display:flex;align-items:center;gap:8px;padding:10px 14px;background:#fffbeb;border:1px solid #fde68a;border-radius:8px;margin-bottom:16px">
                <svg width="14" height="14" fill="none" stroke="#d97706" stroke-width="2" viewBox="0 0 24 24" style="flex-shrink:0">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z"/>
                </svg>
                <span style="font-size:12px;color:#92400e;font-weight:500">Sensitive fields are encrypted. Leave blank to keep existing value.</span>
            </div>
        </div>

        <div>
            <label style="{{ $labelStyle }}">API Key</label>
            <input type="password" wire:model="sms_api_key" placeholder="Leave blank to keep existing" style="{{ $inputStyle }}">
        </div>
        <div>
            <label style="{{ $labelStyle }}">API Secret</label>
            <input type="password" wire:model="sms_api_secret" placeholder="Leave blank to keep existing" style="{{ $inputStyle }}">
        </div>
        <div>
            <label style="{{ $labelStyle }}">Username</label>
            <input type="password" wire:model="sms_username" placeholder="Leave blank to keep existing" style="{{ $inputStyle }}">
        </div>
        <div>
            <label style="{{ $labelStyle }}">Password</label>
            <input type="password" wire:model="sms_password" placeholder="Leave blank to keep existing" style="{{ $inputStyle }}">
        </div>
    </div>
</div>

{{-- OTP Configuration --}}
<div style="{{ $sectionStyle }}">
    <div style="{{ $sectionHead }}">
        <div>
            <div style="font-size:14px;font-weight:700;color:#111827;display:flex;align-items:center;gap:8px">
                <svg width="16" height="16" fill="none" stroke="#7c3aed" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z"/>
                </svg>
                OTP Configuration
            </div>
            <div style="font-size:12px;color:#9ca3af;margin-top:3px">Control OTP behavior for customer registration.</div>
        </div>
    </div>
    <div style="padding:20px;display:grid;grid-template-columns:repeat(3,1fr);gap:16px">
        <div>
            <label style="{{ $labelStyle }}">OTP Length</label>
            <input type="number" wire:model="otp_length" min="4" max="8" style="{{ $inputStyle }}">
        </div>
        <div>
            <label style="{{ $labelStyle }}">Expiry (minutes)</label>
            <input type="number" wire:model="otp_expiry_minutes" min="1" max="30" style="{{ $inputStyle }}">
        </div>
        <div>
            <label style="{{ $labelStyle }}">Max Attempts</label>
            <input type="number" wire:model="otp_max_attempts" min="1" max="10" style="{{ $inputStyle }}">
        </div>
        <div>
            <label style="{{ $labelStyle }}">Max Resend Count</label>
            <input type="number" wire:model="otp_max_resend" min="0" max="10" style="{{ $inputStyle }}">
        </div>
        <div>
            <label style="{{ $labelStyle }}">Resend Cooldown (seconds)</label>
            <input type="number" wire:model="otp_resend_cooldown" min="10" max="300" style="{{ $inputStyle }}">
        </div>
        <div style="display:flex;align-items:center;gap:8px;padding-top:20px">
            <input type="checkbox" wire:model="otp_show_in_local" style="width:15px;height:15px;cursor:pointer">
            <label style="font-size:13px;font-weight:600;color:#374151;cursor:pointer">Show OTP in Local Log</label>
        </div>
        <div style="grid-column:span 3">
            <label style="{{ $labelStyle }}">Message Template</label>
            <textarea wire:model="otp_message_template" rows="3" style="{{ $inputStyle }};font-family:monospace;resize:vertical"></textarea>
            <p style="font-size:11px;color:#9ca3af;margin-top:4px">
                Placeholders: <code style="background:#f3f4f6;padding:1px 5px;border-radius:4px;font-size:11px">{otp}</code>
                <code style="background:#f3f4f6;padding:1px 5px;border-radius:4px;font-size:11px">{minutes}</code>
                <code style="background:#f3f4f6;padding:1px 5px;border-radius:4px;font-size:11px">{app_name}</code>
            </p>
        </div>
    </div>
</div>

{{-- Test SMS --}}
<div style="{{ $sectionStyle }}">
    <div style="{{ $sectionHead }}">
        <div>
            <div style="font-size:14px;font-weight:700;color:#111827;display:flex;align-items:center;gap:8px">
                <svg width="16" height="16" fill="none" stroke="#059669" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5"/>
                </svg>
                Send Test SMS
            </div>
            <div style="font-size:12px;color:#9ca3af;margin-top:3px">Test your SMS configuration using the currently saved provider settings.</div>
        </div>
    </div>
    <div style="padding:20px;display:grid;grid-template-columns:repeat(2,1fr);gap:16px">
        <div>
            <label style="{{ $labelStyle }}">Test Mobile Number</label>
            <input type="tel" wire:model="test_mobile" placeholder="01XXXXXXXXX" maxlength="11" style="{{ $inputStyle }}">
            @error('test_mobile')<p style="{{ $errorStyle }}">{{ $message }}</p>@enderror
        </div>
        <div>
            <label style="{{ $labelStyle }}">Test Message</label>
            <input type="text" wire:model="test_message" maxlength="160" style="{{ $inputStyle }}">
        </div>
        <div style="grid-column:span 2">
            <button wire:click="sendTestSms"
                    style="display:inline-flex;align-items:center;gap:8px;padding:9px 18px;background:#0284c7;color:#fff;border:none;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5"/>
                </svg>
                Send Test SMS
            </button>
            <p style="font-size:11px;color:#9ca3af;margin-top:8px">
                If provider = log, check
                <code style="background:#f3f4f6;padding:1px 5px;border-radius:4px;font-size:11px">storage/logs/laravel.log</code>
            </p>
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
