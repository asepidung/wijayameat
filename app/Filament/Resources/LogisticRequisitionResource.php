<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LogisticRequisitionResource\Pages;
use App\Models\LogisticRequisition;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Filament\Support\RawJs;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;

class LogisticRequisitionResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = LogisticRequisition::class;
    protected static ?string $navigationGroup = 'REQUEST';
    protected static ?int $navigationSort = 11;
    protected static ?string $navigationIcon = 'heroicon-o-document-duplicate';
    protected static ?string $navigationLabel = 'Logistic Request';
    protected static ?string $modelLabel = 'Logistic Request';

    /**
     * Tentukan hak akses apa saja yang HANYA berlaku untuk modul ini
     */
    public static function getPermissionPrefixes(): array
    {
        return [
            'view_any',
            'view',
            'create',
            'update',
            'delete',
            'review',
            'approve',
        ];
    }

    /**
     * Parses a localized number string (Indonesian format) into a standard float.
     * Example: "1.500.000,50" becomes 1500000.50
     */
    public static function parseNumber($value): float
    {
        if (blank($value)) {
            return 0.0;
        }
        $val = (string) $value;
        $val = str_replace('.', '', $val);
        $val = str_replace(',', '.', $val);

        return (float) $val;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Header Information')
                    ->schema([
                        Forms\Components\DatePicker::make('due_date')
                            ->label('Due Date')
                            ->required()
                            ->default(now())
                            // Disables input if the document has moved past the 'Requested' state
                            ->disabled(fn($record) => $record && $record->status !== 'Requested')
                            ->columnSpan(['default' => 12, 'md' => 6]),

                        Forms\Components\Select::make('supplier_id')
                            ->label('Supplier')
                            ->relationship('supplier', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->columnSpan(['default' => 12, 'md' => 6]),

                        // Hidden fields for database storage
                        Forms\Components\Hidden::make('user_id')->default(fn() => Auth::id()),
                        Forms\Components\Hidden::make('total_amount'),
                    ])->columns(['default' => 12, 'md' => 12]),

                Forms\Components\Section::make('Item Details')
                    ->schema([
                        Forms\Components\Repeater::make('items')
                            ->relationship()
                            ->hiddenLabel()
                            ->schema([
                                Forms\Components\Select::make('logistic_item_id')
                                    ->relationship('item', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->placeholder('Select Item...')
                                    ->hiddenLabel()
                                    ->columnSpan(['default' => 12, 'md' => 4]),

                                Forms\Components\TextInput::make('qty')
                                    ->required()
                                    ->default(1)
                                    ->placeholder('Qty')
                                    ->hiddenLabel()
                                    // Formats data fetched from DB to match the mask
                                    ->formatStateUsing(fn($state) => $state ? number_format((float)$state, 2, ',', '.') : '')
                                    // Applies JS masking for thousands and decimals
                                    ->mask(RawJs::make('$money($input, \',\', \'.\', 2)'))
                                    // Converts back to float before saving to DB
                                    ->dehydrateStateUsing(fn($state) => self::parseNumber($state))
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function ($state, $set, $get) {
                                        $qty = self::parseNumber($state);
                                        $price = self::parseNumber($get('price'));
                                        $set('item_total', $qty * $price);
                                    })
                                    ->columnSpan(['default' => 6, 'md' => 2]),

                                Forms\Components\TextInput::make('price')
                                    ->placeholder('Unit Price')
                                    ->hiddenLabel()
                                    ->prefix('Rp')
                                    ->formatStateUsing(fn($state) => $state ? number_format((float)$state, 0, ',', '.') : '')
                                    ->mask(RawJs::make('$money($input, \',\', \'.\', 0)'))
                                    ->dehydrateStateUsing(fn($state) => self::parseNumber($state))
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function ($state, $set, $get) {
                                        $price = self::parseNumber($state);
                                        $qty = self::parseNumber($get('qty'));
                                        $set('item_total', $qty * $price);
                                    })
                                    ->columnSpan(['default' => 6, 'md' => 3]),

                                Forms\Components\TextInput::make('item_total')
                                    ->placeholder('Subtotal')
                                    ->hiddenLabel()
                                    ->prefix('Rp')
                                    ->readOnly()
                                    ->dehydrated(false)
                                    ->formatStateUsing(fn($state) => $state ? number_format((float)$state, 0, ',', '.') : '')
                                    ->mask(RawJs::make('$money($input, \',\', \'.\', 0)'))
                                    ->columnSpan(['default' => 12, 'md' => 3]),
                            ])
                            ->columns(['default' => 12, 'md' => 12])
                            ->live(onBlur: true)
                            // Calculates the grand total dynamically
                            ->afterStateUpdated(function ($state, $set) {
                                $total = collect($state)->sum(function ($item) {
                                    $qty = self::parseNumber($item['qty'] ?? 0);
                                    $price = self::parseNumber($item['price'] ?? 0);
                                    return $qty * $price;
                                });
                                $set('total_amount', $total);
                            })
                            ->defaultItems(1)
                            ->reorderableWithButtons(),
                    ]),

                Forms\Components\Section::make('Summary')
                    ->schema([
                        Forms\Components\Textarea::make('note')
                            ->label('Request Note')
                            ->placeholder('Add extra notes here...')
                            ->columnSpan(['default' => 12, 'md' => 8]),

                        Forms\Components\Placeholder::make('grand_total_display')
                            ->label('Estimated Total')
                            ->content(function ($get) {
                                $items = $get('items') ?? [];
                                $total = 0;
                                foreach ($items as $item) {
                                    $qty = self::parseNumber($item['qty'] ?? 0);
                                    $price = self::parseNumber($item['price'] ?? 0);
                                    $total += $qty * $price;
                                }
                                return 'Rp ' . number_format($total, 0, ',', '.');
                            })
                            ->columnSpan(['default' => 12, 'md' => 4]),
                    ])->columns(['default' => 12, 'md' => 12]),

                Forms\Components\Section::make('Rejection Info')
                    ->description('Information about why this request was rejected.')
                    ->aside() // Biar posisinya menyamping (opsional, bikin rapi)
                    ->schema([
                        Forms\Components\Placeholder::make('reject_note')
                            ->label('Reason')
                            ->content(fn($record) => $record->reject_note)
                            // Ini kuncinya biar background merah, border merah, dan teks tebal
                            ->extraAttributes(['class' => 'text-danger-600 font-bold px-4 py-3 bg-danger-50 border border-danger-300 rounded-lg']),
                    ])
                    ->visible(fn($record) => $record && $record->status === 'Rejected')
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->recordAction(null)
            ->recordUrl(null)
            ->columns([
                Tables\Columns\TextColumn::make('document_number')
                    ->label('Request No.')
                    ->searchable()
                    ->color('primary')
                    ->weight('bold')
                    ->url(fn($record) => route('print.logistic-request', ['id' => $record->id]))
                    ->openUrlInNewTab(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Requester'),
                Tables\Columns\TextColumn::make('supplier.name')
                    ->label('Supplier'),

                /* Menampilkan catatan permintaan dengan batasan karakter agar tabel tidak terlalu lebar */
                Tables\Columns\TextColumn::make('note')
                    ->label('Note')
                    ->limit(50)
                    ->searchable(),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'Requested' => 'gray',
                        'Waiting' => 'warning',
                        'Ordering' => 'info',
                        'PO Created' => 'success',
                        'Rejected' => 'danger',
                        default => 'danger',
                    }),
            ])
            ->filters([
                /* Filter rentang tanggal berdasarkan field created_at dengan nilai default dari awal bulan hingga hari ini */
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label('From Date')
                            ->default(now()->startOfMonth()),
                        Forms\Components\DatePicker::make('created_until')
                            ->label('Until Date')
                            ->default(now()),
                    ])
                    ->query(function (\Illuminate\Database\Eloquent\Builder $query, array $data): \Illuminate\Database\Eloquent\Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn(\Illuminate\Database\Eloquent\Builder $query, $date) => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn(\Illuminate\Database\Eloquent\Builder $query, $date) => $query->whereDate('created_at', '<=', $date),
                            );
                    })
            ])
            ->actions([
                /* Action: Review (For Purchasing/Admin) */
                Tables\Actions\Action::make('review')
                    ->icon('heroicon-s-clipboard-document-check')
                    ->color('warning')
                    ->tooltip('Review Request')
                    ->iconButton()
                    ->visible(function ($record) {
                        /** @var \App\Models\User $user */
                        $user = auth()->user();
                        // Ganti underscore dengan :: di sini
                        return $record->status === 'Requested' && ($user->hasRole('super_admin') || $user->can('review_logistic::requisition'));
                    })
                    ->url(fn($record) => static::getUrl('view', ['record' => $record])),

                /* Action: Re-submit (For Requester to fix Rejected data) */
                Tables\Actions\Action::make('resubmit')
                    ->icon('heroicon-s-arrow-path')
                    ->color('info')
                    ->tooltip('Edit & Re-submit')
                    ->iconButton()
                    ->visible(fn($record) => $record->status === 'Rejected')
                    ->url(fn($record) => static::getUrl('edit', ['record' => $record])),

                /* Action: Edit (Only if status is Requested) */
                Tables\Actions\EditAction::make()
                    ->icon('heroicon-s-pencil-square')
                    ->tooltip('Edit Request')
                    ->iconButton()
                    ->visible(fn($record) => $record->status === 'Requested'),

                /* Action: Cancel/Delete (Only if status is Requested) */
                Tables\Actions\DeleteAction::make()
                    ->icon('heroicon-s-trash')
                    ->color('danger')
                    ->tooltip('Cancel Request')
                    ->iconButton()
                    ->visible(fn($record) => $record->status === 'Requested'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLogisticRequisitions::route('/'),
            'create' => Pages\CreateLogisticRequisition::route('/create'),
            'view' => Pages\ViewLogisticRequisition::route('/{record}'),
            'edit' => Pages\EditLogisticRequisition::route('/{record}/edit'),
        ];
    }
}
