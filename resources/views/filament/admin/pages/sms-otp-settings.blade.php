<x-filament-panels::page>
    <div class="space-y-6">

        {{-- SMS Gateway --}}
        <x-filament::section>
            <x-slot name="heading">SMS Gateway</x-slot>
            <x-slot name="description">Configure the SMS provider used for OTP and notifications.</x-slot>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">

                <div class="sm:col-span-2 flex items-center gap-3">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" wire:model="sms_enabled" class="rounded">
                        <span class="font-semibold text-sm">Enable SMS Sending</span>
                    </label>
                    <span class="text-xs text-gray-400">(When disabled, SMS is only logged)</span>
                </div>

                <div>
                    <label class="block text-sm font-semibold mb-1">Provider</label>
                    <select wire:model="sms_provider" class="w-full border rounded-lg px-3 py-2 text-sm">
                        <option value="log">Log (Local Dev)</option>
                        <option value="generic_http">Generic HTTP</option>
                        <option value="ssl_wireless">SSL Wireless (BD)</option>
                        <option value="bulk_sms_bd">Bulk SMS BD</option>
                        <option value="custom">Custom</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold mb-1">Sender ID / SID</label>
                    <input type="text" wire:model="sms_sender_id" class="w-full border rounded-lg px-3 py-2 text-sm" placeholder="WATERFALL">
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-sm font-semibold mb-1">API URL</label>
                    <input type="url" wire:model="sms_api_url" class="w-full border rounded-lg px-3 py-2 text-sm" placeholder="https://api.provider.com/send">
                </div>

                <div>
                    <label class="block text-sm font-semibold mb-1">HTTP Method</label>
                    <select wire:model="sms_method" class="w-full border rounded-lg px-3 py-2 text-sm">
                        <option value="POST">POST</option>
                        <option value="GET">GET</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold mb-1">Timeout (seconds)</label>
                    <input type="number" wire:model="sms_timeout" class="w-full border rounded-lg px-3 py-2 text-sm" min="1" max="60">
                </div>

                <div>
                    <label class="block text-sm font-semibold mb-1">Mobile Number Param</label>
                    <input type="text" wire:model="sms_mobile_param" class="w-full border rounded-lg px-3 py-2 text-sm" placeholder="to">
                </div>

                <div>
                    <label class="block text-sm font-semibold mb-1">Message Param</label>
                    <input type="text" wire:model="sms_message_param" class="w-full border rounded-lg px-3 py-2 text-sm" placeholder="message">
                </div>

                <div>
                    <label class="block text-sm font-semibold mb-1">From</label>
                    <input type="text" wire:model="sms_from" class="w-full border rounded-lg px-3 py-2 text-sm" placeholder="Sender name or number">
                </div>

                <div>
                    <label class="block text-sm font-semibold mb-1">Extra Params (JSON)</label>
                    <input type="text" wire:model="sms_extra_params" class="w-full border rounded-lg px-3 py-2 text-sm" placeholder='{"key":"value"}'>
                </div>

                {{-- Encrypted fields --}}
                <div class="sm:col-span-2 border-t pt-4 mt-2">
                    <p class="text-xs text-amber-600 mb-3">🔒 Sensitive fields are encrypted. Leave blank to keep existing value.</p>
                </div>

                <div>
                    <label class="block text-sm font-semibold mb-1">API Key</label>
                    <input type="password" wire:model="sms_api_key" class="w-full border rounded-lg px-3 py-2 text-sm" placeholder="Leave blank to keep existing">
                </div>

                <div>
                    <label class="block text-sm font-semibold mb-1">API Secret</label>
                    <input type="password" wire:model="sms_api_secret" class="w-full border rounded-lg px-3 py-2 text-sm" placeholder="Leave blank to keep existing">
                </div>

                <div>
                    <label class="block text-sm font-semibold mb-1">Username</label>
                    <input type="password" wire:model="sms_username" class="w-full border rounded-lg px-3 py-2 text-sm" placeholder="Leave blank to keep existing">
                </div>

                <div>
                    <label class="block text-sm font-semibold mb-1">Password</label>
                    <input type="password" wire:model="sms_password" class="w-full border rounded-lg px-3 py-2 text-sm" placeholder="Leave blank to keep existing">
                </div>
            </div>
        </x-filament::section>

        {{-- OTP Configuration --}}
        <x-filament::section>
            <x-slot name="heading">OTP Configuration</x-slot>
            <x-slot name="description">Control OTP behavior for customer registration.</x-slot>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">

                <div>
                    <label class="block text-sm font-semibold mb-1">OTP Length</label>
                    <input type="number" wire:model="otp_length" class="w-full border rounded-lg px-3 py-2 text-sm" min="4" max="8">
                </div>

                <div>
                    <label class="block text-sm font-semibold mb-1">Expiry (minutes)</label>
                    <input type="number" wire:model="otp_expiry_minutes" class="w-full border rounded-lg px-3 py-2 text-sm" min="1" max="30">
                </div>

                <div>
                    <label class="block text-sm font-semibold mb-1">Max Attempts</label>
                    <input type="number" wire:model="otp_max_attempts" class="w-full border rounded-lg px-3 py-2 text-sm" min="1" max="10">
                </div>

                <div>
                    <label class="block text-sm font-semibold mb-1">Max Resend Count</label>
                    <input type="number" wire:model="otp_max_resend" class="w-full border rounded-lg px-3 py-2 text-sm" min="0" max="10">
                </div>

                <div>
                    <label class="block text-sm font-semibold mb-1">Resend Cooldown (seconds)</label>
                    <input type="number" wire:model="otp_resend_cooldown" class="w-full border rounded-lg px-3 py-2 text-sm" min="10" max="300">
                </div>

                <div class="flex items-center gap-2 pt-5">
                    <input type="checkbox" wire:model="otp_show_in_local" class="rounded">
                    <label class="text-sm font-semibold">Show OTP in Local Log</label>
                </div>

                <div class="sm:col-span-3">
                    <label class="block text-sm font-semibold mb-1">Message Template</label>
                    <textarea wire:model="otp_message_template" rows="3"
                        class="w-full border rounded-lg px-3 py-2 text-sm font-mono"></textarea>
                    <p class="text-xs text-gray-400 mt-1">Placeholders: <code>{otp}</code>, <code>{minutes}</code>, <code>{app_name}</code></p>
                </div>
            </div>
        </x-filament::section>

        {{-- Test SMS --}}
        <x-filament::section>
            <x-slot name="heading">Send Test SMS</x-slot>
            <x-slot name="description">Test your SMS configuration. Uses the currently saved provider settings.</x-slot>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label class="block text-sm font-semibold mb-1">Test Mobile Number</label>
                    <input type="tel" wire:model="test_mobile" class="w-full border rounded-lg px-3 py-2 text-sm"
                        placeholder="01XXXXXXXXX" maxlength="11">
                    @error('test_mobile')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1">Test Message</label>
                    <input type="text" wire:model="test_message" class="w-full border rounded-lg px-3 py-2 text-sm" maxlength="160">
                </div>
            </div>

            <div class="mt-4">
                <x-filament::button wire:click="sendTestSms" color="info" icon="heroicon-o-paper-airplane">
                    Send Test SMS
                </x-filament::button>
                <p class="text-xs text-gray-400 mt-2">If provider = log, check <code>storage/logs/laravel.log</code></p>
            </div>
        </x-filament::section>

        {{-- Save button --}}
        <div class="flex justify-end">
            <x-filament::button wire:click="saveSettings" size="lg" icon="heroicon-o-check">
                Save Settings
            </x-filament::button>
        </div>

    </div>
</x-filament-panels::page>
