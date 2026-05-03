<?php

namespace App\Filament\Admin\Resources\JarPurchaseResource\Pages;

use App\Filament\Admin\Resources\JarPurchaseResource;
use App\Models\JarPurchase;
use App\Services\Accounts\AccountTransactionService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CreateJarPurchase extends CreateRecord
{
    protected static string $resource = JarPurchaseResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        return DB::transaction(function () use ($data) {
            $data = self::calculate($data);
            $data['created_by'] = Auth::id();
            $data['updated_by'] = Auth::id();
            $record = JarPurchase::create($data);
            $this->syncTransaction($record);
            $record->supplier?->recalculateDue();

            return $record->refresh();
        });
    }

    public static function calculate(array $data): array
    {
        $total = (int) ($data['quantity'] ?? 0) * (float) ($data['unit_price'] ?? 0);
        $paid = (float) ($data['paid_amount'] ?? 0);
        $data['total_amount'] = $total;
        $data['due_amount'] = max($total - $paid, 0);
        $data['payment_status'] = $paid <= 0 ? 'unpaid' : ($paid >= $total ? 'paid' : 'partial');

        return $data;
    }

    protected function syncTransaction(JarPurchase $record): void
    {
        if ((float) $record->paid_amount <= 0 || ! $record->payment_account_id) {
            return;
        }

        $service = app(AccountTransactionService::class);
        $transaction = $service->createExpense([
            'transaction_date' => $record->purchase_date,
            'account_category_id' => $service->categoryId('Jar Purchase', 'expense'),
            'payment_account_id' => $record->payment_account_id,
            'amount' => $record->paid_amount,
            'paid_to' => $record->supplier?->name ?? $record->supplier_name,
            'description' => 'Jar purchase payment',
            'attachment' => $record->attachment,
            'status' => 'approved',
            'created_by' => $record->created_by,
        ]);
        $record->forceFill(['account_transaction_id' => $transaction->id])->saveQuietly();
    }
}
