<?php

namespace App\Http\Controllers\Api\Customer\Concerns;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

trait ApiResponse
{
    protected function success(string $message, array $data = [], int $status = 200): JsonResponse
    {
        $body = ['success' => true, 'message' => $message];
        if (! empty($data)) {
            $body['data'] = $data;
        }
        return response()->json($body, $status);
    }

    protected function error(string $message, int $status = 400): JsonResponse
    {
        return response()->json(['success' => false, 'message' => $message], $status);
    }

    protected function validationError(array $errors): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'Validation failed.',
            'errors'  => $errors,
        ], 422);
    }

    /**
     * Resolve locale from request: ?lang=bn|en or Accept-Language header.
     * Defaults to 'bn'.
     */
    protected function resolveLocale(Request $request): string
    {
        $lang = $request->query('lang')
            ?? $request->header('X-App-Lang')
            ?? substr($request->header('Accept-Language', 'bn'), 0, 2);

        return in_array($lang, ['bn', 'en']) ? $lang : 'bn';
    }

    /**
     * Return localized value: Bangla if available and locale=bn, else English.
     */
    protected function localized(?string $bn, ?string $en, string $locale): string
    {
        if ($locale === 'bn' && ! empty($bn)) {
            return $bn;
        }
        return $en ?? '';
    }

    /**
     * Translate a customer status key using lang files.
     */
    protected function translateStatus(string $key, string $locale): string
    {
        app()->setLocale($locale);
        $translated = __('customer.status_' . $key);
        // If translation key not found, fall back to ucfirst of key
        return str_starts_with($translated, 'customer.') ? ucfirst(str_replace('_', ' ', $key)) : $translated;
    }

    /**
     * Translate a slot key.
     */
    protected function translateSlot(?string $key, string $locale): ?string
    {
        if (! $key) return null;
        app()->setLocale($locale);
        $labels = __('customer.slot_labels');
        return is_array($labels) ? ($labels[$key] ?? $key) : $key;
    }

    /**
     * Translate customer type.
     */
    protected function translateType(?string $key, string $locale): ?string
    {
        if (! $key) return null;
        app()->setLocale($locale);
        $labels = __('customer.type_labels');
        return is_array($labels) ? ($labels[$key] ?? ucfirst($key)) : ucfirst($key ?? '');
    }
}
