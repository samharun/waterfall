<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\InvestorResource\Pages;
use App\Models\Investor;
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

class InvestorResource extends Resource
{
    use \App\Filament\Traits\HasRolePermissions;

    protected static string $viewPermission = 'accounts.investors.view';
    protected static string $createPermission = 'accounts.investors.manage';
    protected static string $editPermission = 'accounts.investors.manage';
    protected static string $deletePermission = 'accounts.investors.manage';

    protected static ?string $model = Investor::class;
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-user-plus';
    protected static string|\UnitEnum|null $navigationGroup = 'Accounts';
    protected static ?string $navigationLabel = 'Investors';
    protected static ?int $navigationSort = 8;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Investor')
                ->schema([
                    Grid::make(2)->schema([
                        Forms\Components\TextInput::make('name')->required()->maxLength(255),
                        Forms\Components\TextInput::make('mobile')->maxLength(50),
                        Forms\Components\Select::make('investment_type')->options(Investor::typeLabels())->default('capital')->required(),
                        Forms\Components\TextInput::make('total_invested')->numeric()->prefix('BDT')->default(0)->readOnly(),
                        Forms\Components\TextInput::make('total_returned')->numeric()->prefix('BDT')->default(0)->readOnly(),
                        Forms\Components\TextInput::make('current_balance')->numeric()->prefix('BDT')->default(0)->readOnly(),
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
                Tables\Columns\TextColumn::make('investment_type')->badge(),
                Tables\Columns\TextColumn::make('total_invested')->money('BDT')->sortable(),
                Tables\Columns\TextColumn::make('total_returned')->money('BDT')->sortable(),
                Tables\Columns\TextColumn::make('current_balance')->money('BDT')->sortable(),
                Tables\Columns\IconColumn::make('status')->boolean(),
            ])
            ->filters([Tables\Filters\TernaryFilter::make('status'), Tables\Filters\TrashedFilter::make()])
            ->actions([Actions\ActionGroup::make([Actions\ViewAction::make(), Actions\EditAction::make(), Actions\DeleteAction::make(), Actions\RestoreAction::make()])])
            ->defaultSort('name');
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ListInvestors::route('/'), 'create' => Pages\CreateInvestor::route('/create'), 'edit' => Pages\EditInvestor::route('/{record}/edit')];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()->withoutGlobalScopes([SoftDeletingScope::class]);
    }
}
