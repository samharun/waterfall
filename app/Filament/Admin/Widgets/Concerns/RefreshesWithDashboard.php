<?php

namespace App\Filament\Admin\Widgets\Concerns;

use Livewire\Attributes\On;

trait RefreshesWithDashboard
{
    #[On('dashboard-refresh')]
    public function refreshWithDashboard(): void
    {
        // Livewire re-renders the widget and reloads its data after this action.
    }
}
