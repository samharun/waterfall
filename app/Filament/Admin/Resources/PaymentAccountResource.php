<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\PaymentAccountResource\Pages;
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

class PaymentAccountResource extends Resource
{
    use \App\Filament\Traits\HasRolePermissions;

    protected static string $viewPermission = 'accounts.settings.manage';
    protected static string $createPermission = 'accounts.settings.manage';
    protected static string $editPermission = 'accounts.settings.manage';
    protected static string $deletePermission = 'accounts.settings.manage';

    protected static ?string $model = PaymentAccount::class;
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-wallet';
    protected static string|\UnitEnum|null $navigationGroup = 'Accounts';
    protected static ?string $navigationLabel = 'Payment Accounts';
    protected static ?int $navigationSort = 9;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Payment Account')
                ->schema([
                    Grid::make(2)->schema([
                        Forms\Components\TextInput::make('name')->required()->maxLength(255),
                        Forms\Components\Select::make('type')->options(PaymentAccount::typeLabels())->required(),
                        Forms\Components\TextInput::make('account_no')->maxLength(255),
                        Forms\Components\TextInput::make('opening_balance')->numeric()->prefix('BDT')->default(0)->minValue(0),
                        Forms\Components\TextInput::make('current_balance')->numeric()->prefix('BDT')->default(0)->minValue(0)->helperText('Updated by approved account transactions.'),
                        Forms\Components\Toggle::make('status')->default(true),
                    ]),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('type')->badge(),
                Tables\Columns\TextColumn::make('account_no')->placeholder('-')->searchable(),
                Tables\Columns\TextColumn::make('opening_balance')->label('Opening')->money('BDT')->sortable(),
                Tables\Columns\TextColumn::make('current_balance')->label('Current')->money('BDT')->sortable(),
                Tables\Columns\IconColumn::make('status')->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')->options(PaymentAccount::typeLabels()),
                Tables\Filters\TernaryFilter::make('status'),
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Actions\ActionGroup::make([
                    Actions\ViewAction::make(),
                    Actions\EditAction::make(),
                    Actions\DeleteAction::make(),
                    Actions\RestoreAction::make(),
                ]),
            ])
            ->defaultSort('name');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPaymentAccounts::route('/'),
            'create' => Pages\CreatePaymentAccount::route('/create'),
            'edit' => Pages\EditPaymentAccount::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()->withoutGlobalScopes([SoftDeletingScope::class]);
    }
}
