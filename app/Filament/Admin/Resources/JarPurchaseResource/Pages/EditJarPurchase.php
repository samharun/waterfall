<?php

namespace App\Filament\Admin\Resources\JarPurchaseResource\Pages;

use App\Filament\Admin\Resources\JarPurchaseResource;
use App\Models\JarPurchase;
use App\Services\Accounts\AccountTransactionService;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EditJarPurchase extends EditRecord
{
    protected static string $resource = JarPurchaseResource::class;

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return DB::transaction(function () use ($record, $data) {
            $oldSupplierId = $record->supplier_id;
            $data = CreateJarPurchase::calculate($data);
            $data['updated_by'] = Auth::id();
            $record->update($data);
            $this->syncTransaction($record->refresh());
            $record->supplier?->recalculateDue();
            if ($oldSupplierId && $oldSupplierId !== $record->supplier_id) {
                \App\Models\Supplier::find($oldSupplierId)?->recalculateDue();
            }

            return $record->refresh();
        });
    }

    protected function syncTransaction(JarPurchase $record): void
    {
        $service = app(AccountTransactionService::class);

        if ((float) $record->paid_amount <= 0 || ! $record->payment_account_id) {
            if ($record->accountTransaction) {
                $service->deleteTransaction($record->accountTransaction);
                $record->forceFill(['account_transaction_id' => null])->saveQuietly();
            }
            return;
        }

        $payload = [
            'transaction_date' => $record->purchase_date,
            'account_category_id' => $service->categoryId('Jar Purchase', 'expense'),
            'payment_account_id' => $record->payment_account_id,
            'amount' => $record->paid_amount,
            'paid_to' => $record->supplier?->name ?? $record->supplier_name,
            'description' => 'Jar purchase payment',
            'attachment' => $record->attachment,
            'status' => 'approved',
        ];

        $transaction = $record->accountTransaction
            ? $service->updateTransaction($record->accountTransaction, $payload)
            : $service->createExpense($payload + ['created_by' => $record->created_by]);

        $record->forceFill(['account_transaction_id' => $transaction->id])->saveQuietly();
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()->after(function (JarPurchase $record) {
                if ($record->accountTransaction) {
                    app(AccountTransactionService::class)->deleteTransaction($record->accountTransaction);
                }
                $record->supplier?->recalculateDue();
            }),
            Actions\RestoreAction::make(),
        ];
    }
}
