<?php
namespace App\Filament\Admin\Resources\InvestorTransactionResource\Pages;
use App\Filament\Admin\Resources\InvestorTransactionResource;
use App\Models\InvestorTransaction;
use App\Services\Accounts\AccountTransactionService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
class CreateInvestorTransaction extends CreateRecord
{
    protected static string $resource = InvestorTransactionResource::class;
    protected function handleRecordCreation(array $data): Model
    {
        return DB::transaction(function () use ($data) {
            $data['created_by'] = Auth::id(); $data['updated_by'] = Auth::id();
            $record = InvestorTransaction::create($data); $this->syncAccountTransaction($record); $record->investor->recalculateBalance();
            return $record->refresh();
        });
    }
    protected function syncAccountTransaction(InvestorTransaction $record): void
    {
        $service = app(AccountTransactionService::class);
        $isIncome = $record->transaction_type === 'investment_received';
        $payload = [
            'transaction_date' => $record->transaction_date,
            'account_category_id' => $service->categoryId($isIncome ? 'Investor Fund' : 'Other Expense', $isIncome ? 'income' : 'expense'),
            'payment_account_id' => $record->payment_account_id,
            'amount' => $record->amount,
            'received_from' => $isIncome ? $record->investor->name : null,
            'paid_to' => $isIncome ? null : $record->investor->name,
            'reference_no' => $record->reference_no,
            'description' => $record->note,
            'attachment' => $record->attachment,
            'status' => 'approved',
            'created_by' => $record->created_by,
        ];
        $transaction = $isIncome ? $service->createIncome($payload) : $service->createExpense($payload);
        $record->forceFill(['account_transaction_id' => $transaction->id])->saveQuietly();
    }
}
