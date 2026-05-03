<?php

namespace App\Filament\Admin\Resources\Widgets;

use Filament\Widgets\Widget;

abstract class CustomerMenuStatsWidget extends Widget
{
    protected string $view = 'filament.admin.resources.widgets.customer-menu-stats';

    protected int|string|array $columnSpan = 'full';

    /**
     * @return array<int, array{label: string, value: string, tone?: string, hint?: string}>
     */
    abstract public function getCards(): array;
}
