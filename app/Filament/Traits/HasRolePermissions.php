<?php

namespace App\Filament\Traits;

/**
 * Trait for Filament Resources to enforce role-based permissions.
 * Classes using this trait must declare the four static permission properties.
 */
trait HasRolePermissions
{
    // Classes must declare these:
    // protected static string $viewPermission   = '';
    // protected static string $createPermission = '';
    // protected static string $editPermission   = '';
    // protected static string $deletePermission = '';

    public static function canViewAny(): bool
    {
        $perm = static::$viewPermission ?? '';
        return $perm ? (auth()->user()?->can($perm) ?? false) : true;
    }

    public static function canCreate(): bool
    {
        $perm = static::$createPermission ?? '';
        return $perm ? (auth()->user()?->can($perm) ?? false) : static::canViewAny();
    }

    public static function canEdit($record): bool
    {
        $perm = static::$editPermission ?? '';
        return $perm ? (auth()->user()?->can($perm) ?? false) : static::canViewAny();
    }

    public static function canDelete($record): bool
    {
        $perm = static::$deletePermission ?? '';
        return $perm ? (auth()->user()?->can($perm) ?? false) : false;
    }

    public static function canDeleteAny(): bool
    {
        return static::canDelete(null);
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canViewAny();
    }
}
