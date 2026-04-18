<?php

namespace App\Filament\Admin\Pages\Auth;

use Filament\Auth\Pages\Login as BaseLogin;

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
}