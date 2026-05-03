<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\MaintenanceCostResource\Pages;
use App\Models\BusinessAsset;
use App\Models\MaintenanceCost;
use App\Models\PaymentAccount;
use BackedEnum;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class MaintenanceCostResource extends Resource
{
    use \App\Filament\Traits\HasRolePermissions;

    protected static string $viewPermission = 'accounts.maintenance.view';
    protected static string $createPermission = 'accounts.maintenance.manage';
    protected static string $editPermission = 'accounts.maintenance.manage';
    protected static string $deletePermission = 'accounts.maintenance.manage';

    protected static ?string $model = MaintenanceCost::class;
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-wrench-screwdriver';
    protected static string|\UnitEnum|null $navigationGroup = 'Accounts';
    protected static ?string $navigationLabel = 'Maintenance Cost';
    protected static ?int $navigationSort = 7;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Maintenance')
                ->schema([
                    Grid::make(2)->schema([
                        Forms\Components\DatePicker::make('maintenance_date')->default(now()->toDateString())->required(),
                        Forms\Components\Select::make('business_asset_id')->label('Asset')->options(fn () => BusinessAsset::orderBy('asset_name')->pluck('asset_name', 'id'))->searchable(),
                        Forms\Components\TextInput::make('maintenance_type')->maxLength(255),
                        Forms\Components\TextInput::make('cost')->numeric()->prefix('BDT')->default(0)->minValue(0.01)->required(),
                        Forms\Components\TextInput::make('paid_to')->maxLength(255),
                        Forms\Components\Select::make('payment_account_id')->label('Payment Account')->options(fn () => PaymentAccount::active()->orderBy('name')->pluck('name', 'id'))->searchable()->required(),
                        Forms\Components\DatePicker::make('next_service_date'),
                        Forms\Components\FileUpload::make('attachment')->directory('accounts/maintenance')->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png'])->maxSize(2048),
                    ]),
                    Forms\Components\Textarea::make('description')->rows(3)->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('maintenance_date')->date()->sortable(),
                Tables\Columns\TextColumn::make('businessAsset.asset_name')->label('Asset')->placeholder('-')->searchable(),
                Tables\Columns\TextColumn::make('maintenance_type')->placeholder('-'),
                Tables\Columns\TextColumn::make('cost')->money('BDT')->sortable(),
                Tables\Columns\TextColumn::make('paid_to')->placeholder('-'),
                Tables\Columns\TextColumn::make('next_service_date')->date()->placeholder('-'),
            ])
            ->filters([Tables\Filters\TrashedFilter::make()])
            ->actions([Actions\ActionGroup::make([
                Actions\ViewAction::make(),
                Actions\EditAction::make(),
                Actions\DeleteAction::make()->after(function (MaintenanceCost $record) {
                    if ($record->accountTransaction) {
                        app(\App\Services\Accounts\AccountTransactionService::class)->deleteTransaction($record->accountTransaction);
                    }
                }),
            ])])
            ->defaultSort('maintenance_date', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMaintenanceCosts::route('/'),
            'create' => Pages\CreateMaintenanceCost::route('/create'),
            'edit' => Pages\EditMaintenanceCost::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()->withoutGlobalScopes([SoftDeletingScope::class]);
    }
}
