<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\BusinessAssetResource\Pages;
use App\Models\BusinessAsset;
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
use Illuminate\Support\Facades\Auth;

class BusinessAssetResource extends Resource
{
    use \App\Filament\Traits\HasRolePermissions;

    protected static string $viewPermission = 'accounts.assets.view';
    protected static string $createPermission = 'accounts.assets.manage';
    protected static string $editPermission = 'accounts.assets.manage';
    protected static string $deletePermission = 'accounts.assets.manage';

    protected static ?string $model = BusinessAsset::class;
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static string|\UnitEnum|null $navigationGroup = 'Accounts';
    protected static ?string $navigationLabel = 'Machinery & Equipment';
    protected static ?int $navigationSort = 6;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Asset')
                ->schema([
                    Grid::make(2)->schema([
                        Forms\Components\TextInput::make('asset_name')->required()->maxLength(255),
                        Forms\Components\Select::make('category')->options(BusinessAsset::categoryLabels())->searchable(),
                        Forms\Components\DatePicker::make('purchase_date'),
                        Forms\Components\TextInput::make('purchase_cost')->numeric()->prefix('BDT')->default(0)->minValue(0),
                        Forms\Components\Select::make('supplier_id')->options(fn () => Supplier::orderBy('name')->pluck('name', 'id'))->searchable(),
                        Forms\Components\TextInput::make('supplier_name')->maxLength(255),
                        Forms\Components\DatePicker::make('warranty_date'),
                        Forms\Components\Select::make('current_status')->options(BusinessAsset::statusLabels())->default('active')->required(),
                        Forms\Components\TextInput::make('location')->maxLength(255),
                        Forms\Components\FileUpload::make('attachment')->directory('accounts/assets')->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png'])->maxSize(2048),
                    ]),
                    Forms\Components\Textarea::make('note')->rows(3)->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('asset_name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('category')->badge()->placeholder('-'),
                Tables\Columns\TextColumn::make('purchase_date')->date()->sortable(),
                Tables\Columns\TextColumn::make('purchase_cost')->money('BDT')->sortable(),
                Tables\Columns\TextColumn::make('current_status')->badge()->colors(['success' => 'active', 'danger' => 'damaged', 'warning' => 'repaired', 'gray' => 'sold']),
                Tables\Columns\TextColumn::make('location')->placeholder('-'),
            ])
            ->filters([Tables\Filters\SelectFilter::make('current_status')->options(BusinessAsset::statusLabels()), Tables\Filters\TrashedFilter::make()])
            ->actions([Actions\ActionGroup::make([Actions\ViewAction::make(), Actions\EditAction::make(), Actions\DeleteAction::make(), Actions\RestoreAction::make()])])
            ->defaultSort('asset_name');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBusinessAssets::route('/'),
            'create' => Pages\CreateBusinessAsset::route('/create'),
            'edit' => Pages\EditBusinessAsset::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()->withoutGlobalScopes([SoftDeletingScope::class]);
    }
}
