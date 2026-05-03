<?php
namespace App\Filament\Admin\Resources\MaintenanceCostResource\Pages;
use App\Filament\Admin\Resources\MaintenanceCostResource;
use App\Models\MaintenanceCost;
use App\Services\Accounts\AccountTransactionService;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
class EditMaintenanceCost extends EditRecord
{
    protected static string $resource = MaintenanceCostResource::class;
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return DB::transaction(function () use ($record, $data) {
            $data['updated_by'] = Auth::id(); $record->update($data); $this->syncTransaction($record->refresh()); return $record->refresh();
        });
    }
    protected function syncTransaction(MaintenanceCost $record): void
    {
        $service = app(AccountTransactionService::class);
        $payload = [
            'transaction_date' => $record->maintenance_date,
            'account_category_id' => $service->categoryId('Machinery Maintenance', 'expense') ?? $service->categoryId('Maintenance Equipment', 'expense'),
            'payment_account_id' => $record->payment_account_id,
            'amount' => $record->cost,
            'paid_to' => $record->paid_to,
            'description' => $record->description ?: 'Maintenance cost',
            'attachment' => $record->attachment,
            'status' => 'approved',
        ];
        $transaction = $record->accountTransaction ? $service->updateTransaction($record->accountTransaction, $payload) : $service->createExpense($payload + ['created_by' => $record->created_by]);
        $record->forceFill(['account_transaction_id' => $transaction->id])->saveQuietly();
    }
    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()->after(function (MaintenanceCost $record) { if ($record->accountTransaction) app(AccountTransactionService::class)->deleteTransaction($record->accountTransaction); }), Actions\RestoreAction::make()];
    }
}
