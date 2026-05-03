<?php
namespace App\Filament\Admin\Resources\InvestorTransactionResource\Pages;
use App\Filament\Admin\Resources\InvestorTransactionResource;
use App\Models\Investor;
use App\Models\InvestorTransaction;
use App\Services\Accounts\AccountTransactionService;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
class EditInvestorTransaction extends EditRecord
{
    protected static string $resource = InvestorTransactionResource::class;
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return DB::transaction(function () use ($record, $data) {
            $oldInvestorId = $record->investor_id; $data['updated_by'] = Auth::id(); $record->update($data); $this->syncAccountTransaction($record->refresh());
            $record->investor->recalculateBalance(); if ($oldInvestorId !== $record->investor_id) Investor::find($oldInvestorId)?->recalculateBalance();
            return $record->refresh();
        });
    }
    protected function syncAccountTransaction(InvestorTransaction $record): void
    {
        $service = app(AccountTransactionService::class);
        $isIncome = $record->transaction_type === 'investment_received';
        $payload = [
            'transaction_date' => $record->transaction_date,
            'transaction_type' => $isIncome ? 'income' : 'expense',
            'account_category_id' => $service->categoryId($isIncome ? 'Investor Fund' : 'Other Expense', $isIncome ? 'income' : 'expense'),
            'payment_account_id' => $record->payment_account_id,
            'amount' => $record->amount,
            'received_from' => $isIncome ? $record->investor->name : null,
            'paid_to' => $isIncome ? null : $record->investor->name,
            'reference_no' => $record->reference_no,
            'description' => $record->note,
            'attachment' => $record->attachment,
            'status' => 'approved',
        ];
        $transaction = $record->accountTransaction ? $service->updateTransaction($record->accountTransaction, $payload) : ($isIncome ? $service->createIncome($payload + ['created_by' => $record->created_by]) : $service->createExpense($payload + ['created_by' => $record->created_by]));
        $record->forceFill(['account_transaction_id' => $transaction->id])->saveQuietly();
    }
    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()->after(function (InvestorTransaction $record) { if ($record->accountTransaction) app(AccountTransactionService::class)->deleteTransaction($record->accountTransaction); $record->investor?->recalculateBalance(); }), Actions\RestoreAction::make()];
    }
}
