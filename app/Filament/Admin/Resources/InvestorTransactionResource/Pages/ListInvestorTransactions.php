<?php
namespace App\Filament\Admin\Resources\InvestorTransactionResource\Pages;
use App\Filament\Admin\Resources\InvestorTransactionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
class ListInvestorTransactions extends ListRecords { protected static string $resource = InvestorTransactionResource::class; protected function getHeaderActions(): array { return [Actions\CreateAction::make()]; } }
