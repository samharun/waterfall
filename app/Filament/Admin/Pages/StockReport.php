<?php

namespace App\Filament\Admin\Pages;

use App\Models\Product;
use BackedEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Illuminate\Support\Collection;

class StockReport extends Page implements HasForms
{
    use \App\Filament\Admin\Traits\HasPagePermission;

    protected static string $accessPermission = 'reports.stock.view';
    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-archive-box';

    protected static string|\UnitEnum|null $navigationGroup = 'Reports';

    protected static ?string $navigationLabel = 'Stock Report';

    protected static ?int $navigationSort = 4;

    protected string $view = 'filament.admin.pages.stock-report';

    public ?string $product_type = null;
    public ?string $status       = 'active';
    public bool    $low_stock_only = false;

    public function getProducts(): Collection
    {
        return Product::when($this->product_type,  fn ($q) => $q->where('product_type', $this->product_type))
            ->when($this->status,        fn ($q) => $q->where('status', $this->status))
            ->when($this->low_stock_only, fn ($q) => $q->whereColumn('current_stock', '<=', 'stock_alert_qty'))
            ->orderBy('name')
            ->get();
    }
}
