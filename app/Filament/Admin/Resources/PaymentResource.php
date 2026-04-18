<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\PaymentResource\Pages;
use App\Models\Customer;
use App\Models\Dealer;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\User;
use BackedEnum;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class PaymentResource extends Resource
{
    use \App\Filament\Traits\HasRolePermissions;

    protected static string $viewPermission   = 'payments.view';
    protected static string $createPermission = 'payments.create';
    protected static string $editPermission   = 'payments.update';
    protected static string $deletePermission = 'payments.delete';

    protected static ?string $model = Payment::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-banknotes';

    protected static string|\UnitEnum|null $navigationGroup = 'Billing & Payment';

    protected static ?string $navigationLabel = 'Payments';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([

            Section::make('Payment Information')
                ->icon('heroicon-o-banknotes')
                ->schema([
                    Grid::make(2)->schema([

                        Forms\Components\TextInput::make('payment_no')
                            ->label('Payment No')
                            ->disabled()
                            ->dehydrated(false)
                            ->placeholder('Auto-generated')
                            ->visibleOn('edit'),

                        Forms\Components\Select::make('payment_type')
                            ->label('Payment Type')
                            ->options(Payment::typeLabels())
                            ->default('customer')
                            ->required()
                            ->live(),

                        // Invoice select — filters by payment_type
                        Forms\Components\Select::make('invoice_id')
                            ->label('Invoice (optional)')
                            ->options(function (Get $get) {
                                $type = $get('payment_type') ?? 'customer';
                                return Invoice::where('invoice_type', $type)
                                    ->whereIn('invoice_status', ['issued', 'partial'])
                                    ->with(['customer', 'dealer'])
                                    ->get()
                                    ->mapWithKeys(function (Invoice $inv) {
                                        $party = $inv->customer
                                            ? $inv->customer->name
                                            : ($inv->dealer?->name ?? '—');
                                        return [$inv->id => "{$inv->invoice_no} — {$party} (Due: ৳{$inv->due_amount})"];
                                    });
                            })
                            ->searchable()
                            ->nullable()
                            ->live()
                            ->afterStateUpdated(function ($state, Set $set) {
                                if ($state) {
                                    $invoice = Invoice::find($state);
                                    if ($invoice) {
                                        $set('payment_type', $invoice->invoice_type);
                                        $set('customer_id', $invoice->customer_id);
                                        $set('dealer_id', $invoice->dealer_id);
                                    }
                                }
                            }),

                        Forms\Components\Select::make('customer_id')
                            ->label('Customer')
                            ->options(fn () => Customer::approved()->orderBy('name')
                                ->get()
                                ->mapWithKeys(fn (Customer $c) => [
                                    $c->id => "{$c->customer_id} — {$c->name} ({$c->mobile})",
                                ])
                            )
                            ->searchable()
                            ->live()
                            ->visible(fn (Get $get) => $get('payment_type') === 'customer')
                            ->required(fn (Get $get) => $get('payment_type') === 'customer'),

                        Forms\Components\Select::make('dealer_id')
                            ->label('Dealer')
                            ->options(fn () => Dealer::approved()->orderBy('name')
                                ->get()
                                ->mapWithKeys(fn (Dealer $d) => [
                                    $d->id => "{$d->dealer_code} — {$d->name} ({$d->mobile})",
                                ])
                            )
                            ->searchable()
                            ->live()
                            ->visible(fn (Get $get) => $get('payment_type') === 'dealer')
                            ->required(fn (Get $get) => $get('payment_type') === 'dealer'),

                        Forms\Components\DatePicker::make('payment_date')
                            ->label('Payment Date')
                            ->default(now()->toDateString())
                            ->required(),

                        Forms\Components\TextInput::make('amount')
                            ->label('Amount (৳)')
                            ->numeric()
                            ->prefix('৳')
                            ->required()
                            ->minValue(0.01),

                        Forms\Components\Select::make('payment_method')
                            ->label('Payment Method')
                            ->options(Payment::methodLabels())
                            ->default('cash')
                            ->required(),

                        Forms\Components\TextInput::make('reference_no')
                            ->label('Reference No')
                            ->nullable(),

                        Forms\Components\Select::make('received_by')
                            ->label('Received By')
                            ->options(fn () => User::backOffice()->orderBy('name')->pluck('name', 'id'))
                            ->default(fn () => Auth::id())
                            ->searchable()
                            ->nullable(),
                    ]),

                    Forms\Components\Textarea::make('remarks')
                        ->rows(3)
                        ->nullable()
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('payment_no')
                    ->label('Payment No')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->fontFamily('mono'),

                Tables\Columns\TextColumn::make('payment_type')
                    ->label('Type')
                    ->badge()
                    ->colors(['info' => 'customer', 'warning' => 'dealer']),

                Tables\Columns\TextColumn::make('invoice.invoice_no')
                    ->label('Invoice No')
                    ->searchable()
                    ->sortable()
                    ->fontFamily('mono')
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('party')
                    ->label('Customer / Dealer')
                    ->getStateUsing(function (Payment $record): string {
                        if ($record->payment_type === 'customer' && $record->customer) {
                            return "{$record->customer->customer_id} — {$record->customer->name}";
                        }
                        if ($record->payment_type === 'dealer' && $record->dealer) {
                            return "{$record->dealer->dealer_code} — {$record->dealer->name}";
                        }
                        return '—';
                    })
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->where(function (Builder $q) use ($search) {
                            $q->whereHas('customer', fn ($cq) => $cq
                                ->where('customer_id', 'like', "%{$search}%")
                                ->orWhere('name', 'like', "%{$search}%")
                                ->orWhere('mobile', 'like', "%{$search}%")
                            )->orWhereHas('dealer', fn ($dq) => $dq
                                ->where('dealer_code', 'like', "%{$search}%")
                                ->orWhere('name', 'like', "%{$search}%")
                                ->orWhere('mobile', 'like', "%{$search}%")
                            )->orWhere('reference_no', 'like', "%{$search}%");
                        });
                    }),

                Tables\Columns\TextColumn::make('payment_date')
                    ->label('Date')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('amount')
                    ->label('Amount (৳)')
                    ->numeric(2)
                    ->sortable(),

                Tables\Columns\TextColumn::make('payment_method')
                    ->label('Method')
                    ->badge()
                    ->colors([
                        'success' => 'cash',
                        'info'    => 'bkash',
                        'warning' => 'nagad',
                        'gray'    => 'bank',
                        'info'    => 'card',
                    ]),

                Tables\Columns\TextColumn::make('receivedBy.name')
                    ->label('Received By')
                    ->sortable()
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('collection_source')
                    ->label('Source')
                    ->badge()
                    ->colors([
                        'gray'    => 'admin',
                        'info'    => 'delivery_staff',
                        'success' => 'customer_panel',
                        'warning' => 'dealer_panel',
                    ])
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('order.order_no')
                    ->label('Order No')
                    ->searchable()
                    ->fontFamily('mono')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('delivery.delivery_no')
                    ->label('Delivery No')
                    ->searchable()
                    ->fontFamily('mono')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('payment_type')
                    ->options(Payment::typeLabels()),

                Tables\Filters\SelectFilter::make('payment_method')
                    ->options(Payment::methodLabels()),

                Tables\Filters\SelectFilter::make('collection_source')
                    ->label('Collection Source')
                    ->options(Payment::collectionSourceLabels()),

                Tables\Filters\SelectFilter::make('received_by')
                    ->label('Received By')
                    ->options(fn () => User::backOffice()->orderBy('name')->pluck('name', 'id'))
                    ->searchable(),

                Tables\Filters\Filter::make('payment_date')
                    ->form([
                        Forms\Components\DatePicker::make('from')->label('From'),
                        Forms\Components\DatePicker::make('until')->label('Until'),
                    ])
                    ->query(fn (Builder $q, array $data) => $q
                        ->when($data['from'],  fn ($q) => $q->whereDate('payment_date', '>=', $data['from']))
                        ->when($data['until'], fn ($q) => $q->whereDate('payment_date', '<=', $data['until']))
                    ),

                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Actions\ViewAction::make(),
                Actions\EditAction::make(),
                Actions\Action::make('print_receipt')
                    ->label('Print')
                    ->icon('heroicon-o-printer')
                    ->color('gray')
                    ->visible(fn () => auth()->user()?->can('payments.print'))
                    ->url(fn (Payment $record) => route('admin.payments.print', $record))
                    ->openUrlInNewTab(),
                Actions\DeleteAction::make(),
                Actions\RestoreAction::make(),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                    Actions\RestoreBulkAction::make(),
                    Actions\ForceDeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListPayments::route('/'),
            'create' => Pages\CreatePayment::route('/create'),
            'edit'   => Pages\EditPayment::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()->withoutGlobalScopes([
            SoftDeletingScope::class,
        ]);
    }
}
