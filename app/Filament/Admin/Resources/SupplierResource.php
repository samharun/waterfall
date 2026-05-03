<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\SupplierResource\Pages;
use App\Models\Supplier;
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

class SupplierResource extends Resource
{
    use \App\Filament\Traits\HasRolePermissions;

    protected static string $viewPermission = 'accounts.suppliers.view';
    protected static string $createPermission = 'accounts.suppliers.manage';
    protected static string $editPermission = 'accounts.suppliers.manage';
    protected static string $deletePermission = 'accounts.suppliers.manage';

    protected static ?string $model = Supplier::class;
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-building-storefront';
    protected static string|\UnitEnum|null $navigationGroup = 'Accounts';
    protected static ?string $navigationLabel = 'Suppliers';
    protected static ?int $navigationSort = 8;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Supplier')
                ->schema([
                    Grid::make(2)->schema([
                        Forms\Components\TextInput::make('name')->required()->maxLength(255),
                        Forms\Components\TextInput::make('mobile')->maxLength(50),
                        Forms\Components\TextInput::make('opening_due')->numeric()->prefix('BDT')->default(0)->minValue(0),
                        Forms\Components\TextInput::make('total_purchase')->numeric()->prefix('BDT')->default(0)->readOnly(),
                        Forms\Components\TextInput::make('total_paid')->numeric()->prefix('BDT')->default(0)->readOnly(),
                        Forms\Components\TextInput::make('current_due')->numeric()->prefix('BDT')->default(0)->readOnly(),
                        Forms\Components\Toggle::make('status')->default(true),
                    ]),
                    Forms\Components\Textarea::make('address')->rows(2)->columnSpanFull(),
                    Forms\Components\Textarea::make('note')->rows(3)->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('mobile')->searchable()->placeholder('-'),
                Tables\Columns\TextColumn::make('total_purchase')->money('BDT')->sortable(),
                Tables\Columns\TextColumn::make('total_paid')->money('BDT')->sortable(),
                Tables\Columns\TextColumn::make('current_due')->money('BDT')->sortable(),
                Tables\Columns\IconColumn::make('status')->boolean(),
            ])
            ->filters([Tables\Filters\TernaryFilter::make('status'), Tables\Filters\TrashedFilter::make()])
            ->actions([Actions\ActionGroup::make([Actions\ViewAction::make(), Actions\EditAction::make(), Actions\DeleteAction::make(), Actions\RestoreAction::make()])])
            ->defaultSort('name');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSuppliers::route('/'),
            'create' => Pages\CreateSupplier::route('/create'),
            'edit' => Pages\EditSupplier::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()->withoutGlobalScopes([SoftDeletingScope::class]);
    }
}
