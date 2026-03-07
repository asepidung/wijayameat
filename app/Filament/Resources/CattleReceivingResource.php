<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CattleReceivingResource\Pages;
use App\Models\CattleReceiving;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CattleReceivingResource extends Resource
{
    protected static ?string $model = CattleReceiving::class;
    protected static ?string $navigationIcon = 'heroicon-o-truck';
    protected static ?string $navigationGroup = 'CATTLE';
    protected static ?string $navigationLabel = 'Cattle Receive';
    protected static ?int $navigationSort = 21;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Receiving Header')
                    ->schema([
                        Forms\Components\Hidden::make('cattle_purchase_order_id')->required(),
                        Forms\Components\Hidden::make('supplier_id')->required(),

                        Forms\Components\TextInput::make('po_number_display')
                            ->label('PO Number')
                            ->disabled()->dehydrated(false),

                        Forms\Components\TextInput::make('supplier_name_display')
                            ->label('Supplier')
                            ->disabled()->dehydrated(false),

                        Forms\Components\DatePicker::make('receive_date')
                            ->label('Receive Date')
                            ->default(now())
                            ->required(),

                        Forms\Components\TextInput::make('doc_no')
                            ->label('Document Number')
                            ->placeholder('E.g. SV/2026/001'),

                        Forms\Components\Group::make()
                            ->schema([
                                Forms\Components\Toggle::make('sv_ok')
                                    ->label('SV OK')
                                    ->inline(false),
                                Forms\Components\Toggle::make('skkh_ok')
                                    ->label('SKKH OK')
                                    ->inline(false),
                            ])->columns(2),

                        Forms\Components\Textarea::make('note')
                            ->columnSpanFull(),
                    ])->columns(3),

                Forms\Components\Section::make('Cattle Details (Per Head)')
                    ->schema([
                        Forms\Components\Repeater::make('items')
                            ->relationship('items')
                            ->schema([
                                Forms\Components\Select::make('cattle_category_id')
                                    ->hiddenLabel() // Pake hiddenLabel() sesuai standard v3
                                    ->placeholder('Class')
                                    ->relationship('category', 'name')
                                    ->required()
                                    ->searchable()
                                    ->preload(),

                                Forms\Components\TextInput::make('eartag')
                                    ->hiddenLabel()
                                    ->placeholder('Eartag')
                                    ->required()
                                    ->live(onBlur: true) // Biar langsung merah pas pindah kolom
                                    ->unique('cattle_receiving_items', 'eartag', ignoreRecord: true)
                                    ->rules([
                                        fn(Forms\Get $get): \Closure => function (string $attribute, $value, \Closure $fail) use ($get) {
                                            // Validasi duplikat di DALAM form (Client-side logic)
                                            $items = $get('../../items') ?? [];
                                            $allEartags = collect($items)
                                                ->pluck('eartag')
                                                ->map(fn($val) => strtoupper(trim((string)$val)))
                                                ->filter()
                                                ->toArray();

                                            $counts = array_count_values($allEartags);
                                            if (($counts[strtoupper(trim((string)$value))] ?? 0) > 1) {
                                                $fail('Duplicate eartag in this form!');
                                            }
                                        },
                                    ])
                                    ->extraInputAttributes(['style' => 'text-transform: uppercase']),

                                Forms\Components\TextInput::make('initial_weight')
                                    ->hiddenLabel()
                                    ->placeholder('Weight (Max 800)')
                                    ->required()
                                    ->numeric()
                                    ->inputMode('decimal')
                                    ->maxValue(800) // Kunci di angka 800
                                    ->live(onBlur: true) // Teriak merah kalau > 800 pas kursor pindah
                                    ->suffix('Kg'),

                                Forms\Components\TextInput::make('notes')
                                    ->hiddenLabel()
                                    ->placeholder('Notes'),
                            ])
                            ->columns(4)
                            ->minItems(1)
                            ->addActionLabel('Add Manual Row')
                            ->reorderable(false)
                    ]),
            ]);
    }
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('receiving_number')
                    ->label('GRC Number')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('purchaseOrder.po_number')
                    ->label('PO Number'),

                Tables\Columns\TextColumn::make('supplier.name')
                    ->label('Supplier'),

                Tables\Columns\TextColumn::make('receive_date')
                    ->label('Date')
                    ->date('d M Y'),

                Tables\Columns\TextColumn::make('items_count')
                    ->label('Heads')
                    ->counts('items')
                    ->suffix(' Heads'),

                Tables\Columns\TextColumn::make('creator.name')
                    ->label('Received By')
                    ->badge()
                    ->color('gray'),

            ])
            ->recordUrl(
                fn(CattleReceiving $record): string => Pages\ViewCattleReceiving::getUrl([$record->id]),
            )
            ->actions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCattleReceivings::route('/'),
            'create' => Pages\CreateCattleReceiving::route('/create'),
            'draft' => Pages\DraftCattleReceiving::route('/draft'),
            // Pastikan halaman VIEW terdaftar di sini
            'view' => Pages\ViewCattleReceiving::route('/{record}'),
            'edit' => Pages\EditCattleReceiving::route('/{record}/edit'),
        ];
    }
}
