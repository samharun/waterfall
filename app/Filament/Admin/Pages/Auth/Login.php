<?php

namespace App\Filament\Admin\Pages\Auth;

use Filament\Auth\Pages\Login as BaseLogin;
use Illuminate\Contracts\Support\Htmlable;

class Login extends BaseLogin
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    public function getTitle(): string
    {
        return 'Admin Sign in';
    }

    public function getView(): string
    {
        return 'filament.auth.login';
    }

    public function getHeading(): string | Htmlable | null
    {
        return null;
    }

    public function getSubheading(): string | Htmlable | null
    {
        return null;
    }

    public function hasLogo(): bool
    {
        return false;
    }
}
