<?php

namespace App\Http\Requests\Api\Customer;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDeviceTokenRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'device_token' => ['required', 'string', 'max:2048'],
            'platform' => ['nullable', Rule::in(['android', 'ios'])],
            'device_name' => ['nullable', 'string', 'max:255'],
            'app_version' => ['nullable', 'string', 'max:100'],
        ];
    }
}
