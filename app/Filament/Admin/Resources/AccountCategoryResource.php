<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\AccountCategoryResource\Pages;
use App\Models\AccountCategory;
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

class AccountCategoryResource extends Resource
{
    use \App\Filament\Traits\HasRolePermissions;

    protected static string $viewPermission = 'accounts.settings.manage';
    protected static string $createPermission = 'accounts.settings.manage';
    protected static string $editPermission = 'accounts.settings.manage';
    protected static string $deletePermission = 'accounts.settings.manage';

    protected static ?string $model = AccountCategory::class;
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-tag';
    protected static string|\UnitEnum|null $navigationGroup = 'Accounts';
    protected static ?string $navigationLabel = 'Categories';
    protected static ?int $navigationSort = 10;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Category')
                ->schema([
                    Grid::make(2)->schema([
                        Forms\Components\TextInput::make('name')->required()->maxLength(255),
                        Forms\Components\Select::make('type')->options(AccountCategory::typeLabels())->required(),
                        Forms\Components\Toggle::make('status')->default(true),
                    ]),
                    Forms\Components\Textarea::make('description')->rows(3)->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('type')->badge()->colors(['success' => 'income', 'danger' => 'expense']),
                Tables\Columns\IconColumn::make('status')->boolean(),
                Tables\Columns\TextColumn::make('createdBy.name')->label('Created By')->placeholder('-')->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')->options(AccountCategory::typeLabels()),
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
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                    Actions\RestoreBulkAction::make(),
                ]),
            ])
            ->defaultSort('type');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAccountCategories::route('/'),
            'create' => Pages\CreateAccountCategory::route('/create'),
            'edit' => Pages\EditAccountCategory::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()->withoutGlobalScopes([SoftDeletingScope::class]);
    }
}
