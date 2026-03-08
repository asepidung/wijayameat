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
                        // Bagian Repeater di CattleWeighingResource.php

                        Forms\Components\Repeater::make('items')
                            // ->relationship('items')  <--- HAPUS BARIS INI
                            ->schema([
                                Forms\Components\Hidden::make('cattle_receiving_item_id'),

                                Forms\Components\TextInput::make('eartag_display')
                                    ->hiddenLabel()
                                    ->placeholder('Eartag')
                                    ->readOnly() // Pake readOnly biar data tetep nempel tapi gak bisa diketik
                                    ->dehydrated(false), // Jangan disimpan ke tabel timbangan

                                Forms\Components\TextInput::make('initial_weight_display')
                                    ->hiddenLabel()
                                    ->placeholder('Initial Weight')
                                    ->suffix('Kg')
                                    ->readOnly() // Pake readOnly
                                    ->dehydrated(false),

                                Forms\Components\TextInput::make('weight')
                                    ->hiddenLabel()
                                    ->placeholder('Actual Weight')
                                    ->required()
                                    ->numeric()
                                    ->inputMode('decimal')
                                    ->suffix('Kg'),

                                Forms\Components\TextInput::make('notes')
                                    ->hiddenLabel()
                                    ->placeholder('Notes'),
                            ])
                            ->columns(4)
                            ->addable(false) // MATIKAN TOMBOL ADD
                            ->deletable(false) // MATIKAN TOMBOL DELETE
                            ->reorderable(false),
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
