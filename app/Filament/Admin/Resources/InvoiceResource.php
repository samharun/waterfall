<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\InvoiceResource\Pages;
use App\Models\Customer;
use App\Models\Dealer;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\User;
use BackedEnum;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
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

class InvoiceResource extends Resource
{
    use \App\Filament\Traits\HasRolePermissions;

    protected static string $viewPermission   = 'invoices.view';
    protected static string $createPermission = 'invoices.create';
    protected static string $editPermission   = 'invoices.update';
    protected static string $deletePermission = 'invoices.delete';

    protected static ?string $model = Invoice::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    protected static string|\UnitEnum|null $navigationGroup = 'Administration';

    protected static ?string $navigationLabel = 'Invoices';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([

            Section::make('Invoice Information')
                ->icon('heroicon-o-document-text')
                ->schema([
                    Grid::make(2)->schema([

                        Forms\Components\TextInput::make('invoice_no')
                            ->label('Invoice No')
                            ->disabled()
                            ->dehydrated(false)
                            ->placeholder('Auto-generated')
                            ->visibleOn('edit'),

                        Forms\Components\Select::make('invoice_type')
                            ->label('Invoice Type')
                            ->options(Invoice::typeLabels())
                            ->default('customer')
                            ->required()
                            ->live(),

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
                            ->visible(fn (Get $get) => $get('invoice_type') === 'customer')
                            ->required(fn (Get $get) => $get('invoice_type') === 'customer')
                            ->afterStateUpdated(function ($state, Set $set, $record) {
                                if ($state && ! $record) {
                                    $customer = Customer::find($state);
                                    if ($customer) {
                                        $set('previous_due', (float) $customer->current_due);
                                    }
                                }
                            }),

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
                            ->visible(fn (Get $get) => $get('invoice_type') === 'dealer')
                            ->required(fn (Get $get) => $get('invoice_type') === 'dealer')
                            ->afterStateUpdated(function ($state, Set $set, $record) {
                                if ($state && ! $record) {
                                    $dealer = Dealer::find($state);
                                    if ($dealer) {
                                        $set('previous_due', (float) $dealer->current_due);
                                    }
                                }
                            }),

                        Forms\Components\Select::make('billing_month')
                            ->label('Billing Month')
                            ->options(Invoice::monthLabels())
                            ->default(now()->month)
                            ->nullable(),

                        Forms\Components\TextInput::make('billing_year')
                            ->label('Billing Year')
                            ->numeric()
                            ->default(now()->year)
                            ->minValue(2020)
                            ->maxValue(2099)
                            ->nullable(),

                        Forms\Components\DatePicker::make('invoice_date')
                            ->label('Invoice Date')
                            ->default(now()->toDateString())
                            ->required(),

                        Forms\Components\DatePicker::make('due_date')
                            ->label('Due Date')
                            ->nullable(),

                        Forms\Components\Select::make('invoice_status')
                            ->label('Status')
                            ->options(Invoice::statusLabels())
                            ->default('draft')
                            ->required(),
                    ]),
                ]),

            Section::make('Amount Summary')
                ->icon('heroicon-o-calculator')
                ->schema([
                    Grid::make(2)->schema([

                        Forms\Components\TextInput::make('subtotal')
                            ->label('Subtotal (৳)')
                            ->numeric()
                            ->prefix('৳')
                            ->default(0)
                            ->minValue(0)
                            ->required()
                            ->live(),

                        Forms\Components\TextInput::make('previous_due')
                            ->label('Previous Due (৳)')
                            ->numeric()
                            ->prefix('৳')
                            ->default(0)
                            ->minValue(0)
                            ->live(),

                        Forms\Components\TextInput::make('total_amount')
                            ->label('Total Amount (৳)')
                            ->numeric()
                            ->prefix('৳')
                            ->disabled()
                            ->dehydrated(true)
                            ->default(0),

                        Forms\Components\TextInput::make('paid_amount')
                            ->label('Paid Amount (৳)')
                            ->numeric()
                            ->prefix('৳')
                            ->disabled()
                            ->dehydrated(true)
                            ->default(0),

                        Forms\Components\TextInput::make('due_amount')
                            ->label('Due Amount (৳)')
                            ->numeric()
                            ->prefix('৳')
                            ->disabled()
                            ->dehydrated(true)
                            ->default(0),

                        Forms\Components\Textarea::make('remarks')
                            ->rows(3)
                            ->nullable()
                            ->columnSpanFull(),
                    ]),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('invoice_no')
                    ->label('Invoice No')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->fontFamily('mono'),

                Tables\Columns\TextColumn::make('invoice_type')
                    ->label('Type')
                    ->badge()
                    ->colors(['info' => 'customer', 'warning' => 'dealer']),

                Tables\Columns\TextColumn::make('party')
                    ->label('Customer / Dealer')
                    ->getStateUsing(function (Invoice $record): string {
                        if ($record->invoice_type === 'customer' && $record->customer) {
                            return "{$record->customer->customer_id} — {$record->customer->name}";
                        }
                        if ($record->invoice_type === 'dealer' && $record->dealer) {
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
                            );
                        });
                    }),

                Tables\Columns\TextColumn::make('billing_month')
                    ->label('Month')
                    ->formatStateUsing(fn ($state) => $state ? Invoice::monthLabels()[$state] ?? $state : '—')
                    ->sortable(),

                Tables\Columns\TextColumn::make('billing_year')
                    ->label('Year')
                    ->sortable(),

                Tables\Columns\TextColumn::make('invoice_date')
                    ->label('Invoice Date')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('due_date')
                    ->label('Due Date')
                    ->date()
                    ->sortable()
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Total (৳)')
                    ->numeric(2)
                    ->sortable(),

                Tables\Columns\TextColumn::make('paid_amount')
                    ->label('Paid (৳)')
                    ->numeric(2)
                    ->sortable(),

                Tables\Columns\TextColumn::make('due_amount')
                    ->label('Due (৳)')
                    ->numeric(2)
                    ->sortable()
                    ->color(fn ($state) => (float) $state > 0 ? 'danger' : 'success'),

                Tables\Columns\TextColumn::make('invoice_status')
                    ->label('Status')
                    ->badge()
                    ->colors([
                        'gray'    => 'draft',
                        'info'    => 'issued',
                        'warning' => 'partial',
                        'success' => 'paid',
                        'danger'  => 'cancelled',
                    ]),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('invoice_type')
                    ->options(Invoice::typeLabels()),

                Tables\Filters\SelectFilter::make('invoice_status')
                    ->options(Invoice::statusLabels()),

                Tables\Filters\SelectFilter::make('billing_month')
                    ->options(Invoice::monthLabels()),

                Tables\Filters\SelectFilter::make('billing_year')
                    ->options(fn () => Invoice::distinct()->pluck('billing_year', 'billing_year')
                        ->filter()
                        ->sort()
                        ->toArray()
                    ),

                Tables\Filters\Filter::make('invoice_date')
                    ->form([
                        Forms\Components\DatePicker::make('from')->label('From'),
                        Forms\Components\DatePicker::make('until')->label('Until'),
                    ])
                    ->query(fn (Builder $q, array $data) => $q
                        ->when($data['from'],  fn ($q) => $q->whereDate('invoice_date', '>=', $data['from']))
                        ->when($data['until'], fn ($q) => $q->whereDate('invoice_date', '<=', $data['until']))
                    ),

                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Actions\ActionGroup::make([
                    Actions\ViewAction::make(),
                    Actions\EditAction::make(),

                // Mark Issued
                Actions\Action::make('mark_issued')
                    ->label('Issue')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('info')
                    ->visible(fn (Invoice $r) => $r->invoice_status === 'draft')
                    ->requiresConfirmation()
                    ->action(function (Invoice $record) {
                        $record->markIssued();
                        Notification::make()->title('Invoice issued')->success()->send();
                    }),

                // Cancel Invoice
                Actions\Action::make('cancel_invoice')
                    ->label('Cancel')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (Invoice $r) => ! in_array($r->invoice_status, ['paid', 'cancelled']))
                    ->requiresConfirmation()
                    ->action(function (Invoice $record) {
                        $record->markCancelled();
                        Notification::make()->title('Invoice cancelled')->warning()->send();
                    }),

                // Record Payment inline
                Actions\Action::make('record_payment')
                    ->label('Record Payment')
                    ->icon('heroicon-o-banknotes')
                    ->color('success')
                    ->visible(fn (Invoice $r) => in_array($r->invoice_status, ['issued', 'partial']))
                    ->form(fn (Invoice $record) => [
                        Forms\Components\DatePicker::make('payment_date')
                            ->label('Payment Date')
                            ->default(now()->toDateString())
                            ->required(),

                        Forms\Components\TextInput::make('amount')
                            ->label('Amount (৳)')
                            ->numeric()
                            ->prefix('৳')
                            ->required()
                            ->minValue(0.01)
                            ->maxValue((float) $record->due_amount)
                            ->helperText("Max: ৳{$record->due_amount}"),

                        Forms\Components\Select::make('payment_method')
                            ->label('Payment Method')
                            ->options(Payment::methodLabels())
                            ->default('cash')
                            ->required(),

                        Forms\Components\TextInput::make('reference_no')
                            ->label('Reference No')
                            ->nullable(),

                        Forms\Components\Textarea::make('remarks')
                            ->rows(2)
                            ->nullable(),
                    ])
                    ->action(function (Invoice $record, array $data) {
                        Payment::create([
                            'payment_no'     => Payment::generatePaymentNo(),
                            'payment_type'   => $record->invoice_type,
                            'customer_id'    => $record->customer_id,
                            'dealer_id'      => $record->dealer_id,
                            'invoice_id'     => $record->id,
                            'payment_date'   => $data['payment_date'],
                            'amount'         => $data['amount'],
                            'payment_method' => $data['payment_method'],
                            'reference_no'   => $data['reference_no'] ?? null,
                            'received_by'    => Auth::id(),
                            'remarks'        => $data['remarks'] ?? null,
                        ]);
                        // Payment model boot handles sync automatically
                        Notification::make()->title('Payment recorded')->success()->send();
                    }),

                Actions\Action::make('print_invoice')
                    ->label('Print')
                    ->icon('heroicon-o-printer')
                    ->color('gray')
                    ->visible(fn () => auth()->user()?->can('invoices.print'))
                    ->url(fn (Invoice $record) => route('admin.invoices.print', $record))
                    ->openUrlInNewTab(),

                    Actions\DeleteAction::make(),
                    Actions\RestoreAction::make(),
                ])->label('Actions'),
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
            'index'  => Pages\ListInvoices::route('/'),
            'create' => Pages\CreateInvoice::route('/create'),
            'edit'   => Pages\EditInvoice::route('/{record}/edit'),
        ];
    }
    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()->withoutGlobalScopes([
            SoftDeletingScope::class,
        ]);
    }
}

