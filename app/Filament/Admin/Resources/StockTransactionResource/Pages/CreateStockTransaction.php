<?php

namespace App\Filament\Admin\Resources\StockTransactionResource\Pages;

use App\Filament\Admin\Resources\StockTransactionResource;
use App\Models\StockTransaction;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateStockTransaction extends CreateRecord
{
    protected static string $resource = StockTransactionResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['transaction_no'] = StockTransaction::generateTransactionNo();
        $data['created_by']     = Auth::id();

        // Clear adjustment_direction for non-adjustment types
        if ($data['transaction_type'] !== 'adjustment') {
            $data['adjustment_direction'] = null;
        }

        return $data;
    }
}
