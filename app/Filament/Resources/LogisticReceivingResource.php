<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LogisticReceivingResource\Pages;
use App\Models\LogisticReceiving;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists;
use Filament\Infolists\Infolist;

class LogisticReceivingResource extends Resource
{
    protected static ?string $model = LogisticReceiving::class;
    protected static ?string $navigationIcon = 'heroicon-o-truck';
    protected static ?string $navigationGroup = 'GOOD RECEIPT';
    protected static ?string $navigationLabel = 'GR Logistic';
    protected static ?int $navigationSort = 17;



    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Receiving Information')
                    ->schema([
                        Forms\Components\Hidden::make('logistic_purchase_order_id')->required(),
                        Forms\Components\Hidden::make('supplier_id')->required(),

                        Forms\Components\TextInput::make('po_number_display')
                            ->label('PO Number')
                            ->disabled()
                            ->dehydrated(false),

                        Forms\Components\TextInput::make('supplier_name_display')
                            ->label('Supplier')
                            ->disabled()
                            ->dehydrated(false),

                        Forms\Components\DatePicker::make('receive_date')
                            ->label('Receiving Date')
                            ->required(),

                        /* Required-nya udah dihapus, sekarang opsional */
                        Forms\Components\TextInput::make('sj_number')
                            ->label('Delivery Order Number (SJ)'),

                        Forms\Components\Textarea::make('note')
                            ->label('Receiving Note')
                            ->columnSpanFull(),
                    ])->columns(2),

                Forms\Components\Section::make('Item Details (Physical Check)')
                    ->schema([
                        Forms\Components\Repeater::make('items')
                            /* ->relationship() SENGAJA DIHAPUS BIAR FILAMENT GAK SOK PINTER */
                            ->schema([
                                Forms\Components\Hidden::make('logistic_item_id'),

                                Forms\Components\TextInput::make('item_name_display')
                                    ->label('Item Description')
                                    ->readOnly()
                                    ->dehydrated(false)
                                    ->columnSpan(6),

                                Forms\Components\TextInput::make('qty_ordered')
                                    ->label('Pending PO Qty')
                                    ->numeric()
                                    ->readOnly()
                                    ->dehydrated(false)
                                    ->columnSpan(3),

                                Forms\Components\TextInput::make('qty_received')
                                    ->label('Received Qty')
                                    ->numeric()
                                    ->required()
                                    ->columnSpan(3),
                            ])
                            ->columns(12)
                            ->addable(false)
                            ->deletable(true)
                    ])
            ]);
    }

    /* Tambahin Use Infolist ini di bagian paling atas file kalau belum ada */
    // use Filament\Infolists;
    // use Filament\Infolists\Infolist;

    public static function infolist(Infolists\Infolist $infolist): Infolists\Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Receiving Information')
                    ->schema([
                        Infolists\Components\TextEntry::make('receiving_number')
                            ->label('GR Number')
                            ->weight('bold')
                            ->color('primary'),
                        Infolists\Components\TextEntry::make('purchaseOrder.po_number')
                            ->label('PO Number'),
                        Infolists\Components\TextEntry::make('supplier.name')
                            ->label('Supplier'),
                        Infolists\Components\TextEntry::make('receive_date')
                            ->label('Receiving Date')
                            ->date('d F Y'),
                        Infolists\Components\TextEntry::make('sj_number')
                            ->label('Delivery Order (SJ)'),
                        Infolists\Components\TextEntry::make('creator.name')
                            ->label('Received By'),
                        Infolists\Components\TextEntry::make('note')
                            ->label('Note')
                            ->columnSpanFull(),
                    ])->columns(3),

                Infolists\Components\Section::make('Received Items')
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('items')
                            ->label('') // Sengaja dikosongin biar rapi
                            ->schema([
                                Infolists\Components\TextEntry::make('item.name')
                                    ->label('Item Description')
                                    ->weight('bold')
                                    ->columnSpan(3),
                                Infolists\Components\TextEntry::make('qty_received')
                                    ->label('Qty')
                                    ->badge() // Biar angkanya ada di dalam kotak cantik
                                    ->color('success')
                                    ->columnSpan(1),
                            ])
                            ->columns(4)
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('receiving_number')->label('No. GR')->searchable()->weight('bold'),
                Tables\Columns\TextColumn::make('receive_date')->label('Tgl. GR')->date('d-M-Y')->sortable(),
                Tables\Columns\TextColumn::make('purchaseOrder.po_number')->label('No. PO')->searchable(),
                Tables\Columns\TextColumn::make('sj_number')->label('Surat Jalan')->searchable(),
                Tables\Columns\TextColumn::make('supplier.name')->label('Supplier')->searchable(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ]);
    }
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLogisticReceivings::route('/'),
            'create' => Pages\CreateLogisticReceiving::route('/create'),
            // 'view' => Pages\ViewLogisticReceiving::route('/{record}'),

            /* Tambahin baris ini */
            'draft' => Pages\DraftLogisticReceiving::route('/draft'),
        ];
    }
}
