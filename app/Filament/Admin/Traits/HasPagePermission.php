<?php

namespace App\Filament\Admin\Traits;

trait HasPagePermission
{
    // Classes must declare: protected static string $accessPermission = '';

    public static function canAccess(): bool
    {
        $perm = static::$accessPermission ?? '';

        if (! $perm) {
            return auth()->user()?->isBackOffice() ?? false;
        }

        return auth()->user()?->can($perm) ?? false;
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }
}
