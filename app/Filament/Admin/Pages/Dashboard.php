<?php

namespace App\Filament\Admin\Pages;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static ?string $title = 'Operations Dashboard';

    public string $lastUpdatedAt = '';

    public function mount(): void
    {
        $this->lastUpdatedAt = now()->format('h:i:s A');
    }

    public function getSubheading(): ?string
    {
        return "Live operational overview · Updated {$this->lastUpdatedAt}";
    }

    public function getColumns(): int|array
    {
        return [
            'default' => 1,
            'lg' => 2,
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('refreshDashboard')
                ->label('Refresh dashboard')
                ->icon('heroicon-o-arrow-path')
                ->color('primary')
                ->action(function (): void {
                    $this->lastUpdatedAt = now()->format('h:i:s A');
                    $this->dispatch('dashboard-refresh');

                    Notification::make()
                        ->title('Dashboard refreshed')
                        ->success()
                        ->send();
                }),
        ];
    }
}
