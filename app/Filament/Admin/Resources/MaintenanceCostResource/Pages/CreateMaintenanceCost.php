<?php
namespace App\Filament\Admin\Resources\MaintenanceCostResource\Pages;
use App\Filament\Admin\Resources\MaintenanceCostResource;
use App\Models\MaintenanceCost;
use App\Services\Accounts\AccountTransactionService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
class CreateMaintenanceCost extends CreateRecord
{
    protected static string $resource = MaintenanceCostResource::class;
    protected function handleRecordCreation(array $data): Model
    {
        return DB::transaction(function () use ($data) {
            $data['created_by'] = Auth::id(); $data['updated_by'] = Auth::id();
            $record = MaintenanceCost::create($data); $this->syncTransaction($record);
            return $record->refresh();
        });
    }
    protected function syncTransaction(MaintenanceCost $record): void
    {
        $service = app(AccountTransactionService::class);
        $transaction = $service->createExpense([
            'transaction_date' => $record->maintenance_date,
            'account_category_id' => $service->categoryId('Machinery Maintenance', 'expense') ?? $service->categoryId('Maintenance Equipment', 'expense'),
            'payment_account_id' => $record->payment_account_id,
            'amount' => $record->cost,
            'paid_to' => $record->paid_to,
            'description' => $record->description ?: 'Maintenance cost',
            'attachment' => $record->attachment,
            'status' => 'approved',
            'created_by' => $record->created_by,
        ]);
        $record->forceFill(['account_transaction_id' => $transaction->id])->saveQuietly();
    }
}
