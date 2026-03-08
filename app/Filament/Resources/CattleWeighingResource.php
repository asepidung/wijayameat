<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CattleWeighingResource\Pages;
use App\Models\CattleWeighing;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CattleWeighingResource extends Resource
{
    protected static ?string $model = CattleWeighing::class;
    protected static ?string $navigationIcon = 'heroicon-o-scale';
    protected static ?string $navigationGroup = 'CATTLE';
    protected static ?string $navigationLabel = 'Cattle Weighing';
    protected static ?int $navigationSort = 21;

    // ... [Schema Form di CattleWeighingResource.php] ...
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Weighing Header')
                    ->schema([
                        Forms\Components\Hidden::make('cattle_receiving_id')->required(),
                        Forms\Components\TextInput::make('grc_number_display')->label('GRC Number')->disabled()->dehydrated(false),
                        Forms\Components\TextInput::make('po_number_display')->label('PO Number')->disabled()->dehydrated(false),
                        Forms\Components\TextInput::make('supplier_name_display')->label('Supplier')->disabled()->dehydrated(false),
                        Forms\Components\DatePicker::make('weigh_date')->label('Weighing Date')->default(now())->required(),
                        Forms\Components\Textarea::make('note')->label('General Note')->columnSpanFull(),
                    ])->columns(2),

                Forms\Components\Section::make('Cattle List (Actual Weight)')
                    ->schema([
                        // TRIK: Bikin Label Paling Atas Sekali (Pura-pura jadi header tabel)
                        Forms\Components\Grid::make(4)
                            ->schema([
                                Forms\Components\Placeholder::make('lbl_eartag')->hiddenLabel()->content(new \Illuminate\Support\HtmlString('<strong>EARTAG</strong>')),
                                Forms\Components\Placeholder::make('lbl_initial')->hiddenLabel()->content(new \Illuminate\Support\HtmlString('<strong>INITIAL WEIGHT</strong>')),
                                Forms\Components\Placeholder::make('lbl_actual')->hiddenLabel()->content(new \Illuminate\Support\HtmlString('<strong>ACTUAL WEIGHT</strong>')),
                                Forms\Components\Placeholder::make('lbl_notes')->hiddenLabel()->content(new \Illuminate\Support\HtmlString('<strong>NOTES</strong>')),
                            ]),

                        Forms\Components\Repeater::make('weighing_items')
                            ->hiddenLabel()
                            ->schema([
                                Forms\Components\Hidden::make('cattle_receiving_item_id'),
                                Forms\Components\TextInput::make('eartag_display')->hiddenLabel()->readOnly()->dehydrated(false),
                                Forms\Components\TextInput::make('initial_weight_display')->hiddenLabel()->suffix('Kg')->readOnly()->dehydrated(false),
                                Forms\Components\TextInput::make('weight')->hiddenLabel()->required()->numeric()->inputMode('decimal')->suffix('Kg'),
                                Forms\Components\TextInput::make('notes')->hiddenLabel(),
                            ])
                            ->columns(4)
                            ->addable(false)
                            ->deletable(false)
                            ->reorderable(false),
                    ]),

                // SEKSI BARU: HASIL PERHITUNGAN AKHIR
                Forms\Components\Section::make('Calculation Results')
                    ->schema([
                        Forms\Components\TextInput::make('summary_heads')
                            ->label('Total Heads')
                            ->readOnly()->dehydrated(false),
                        Forms\Components\TextInput::make('summary_initial')
                            ->label('Total Initial Weight')
                            ->suffix('Kg')->readOnly()->dehydrated(false),
                        Forms\Components\TextInput::make('summary_actual')
                            ->label('Total Actual Weight')
                            ->suffix('Kg')->readOnly()->dehydrated(false),
                        Forms\Components\TextInput::make('summary_diff')
                            ->label('Weight Difference')
                            ->suffix('Kg')->readOnly()->dehydrated(false)
                            // Warnain ijo kalau untung (gain), merah kalau susut
                            ->extraInputAttributes(fn($state) => [
                                'style' => (float)$state < 0 ? 'color: red; font-weight: bold;' : 'color: green; font-weight: bold;'
                            ]),
                    ])->columns(4),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('weigh_no')->label('Weighing No')->weight('bold')->searchable(),
                Tables\Columns\TextColumn::make('receiving.receiving_number')->label('GRC No')->searchable(),
                Tables\Columns\TextColumn::make('receiving.purchaseOrder.po_number')->label('PO No'),
                Tables\Columns\TextColumn::make('weigh_date')->label('Date')->date('d M Y'),
                Tables\Columns\TextColumn::make('creator.name')->label('Weigher')->badge()->color('success'),
                Tables\Columns\TextColumn::make('items_count')->label('Heads')->counts('items')->suffix(' Heads'),
            ])
            ->recordUrl(fn(CattleWeighing $record): string => Pages\ViewCattleWeighing::getUrl([$record->id]))
            ->actions([]); // Tombol view dihilangkan biar bersih (bisa diklik di barisnya)
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCattleWeighings::route('/'),
            'create' => Pages\CreateCattleWeighing::route('/create'),
            'draft' => Pages\DraftCattleWeighing::route('/draft'), // Tambah halaman draft
            'view' => Pages\ViewCattleWeighing::route('/{record}'),
            'edit' => Pages\EditCattleWeighing::route('/{record}/edit'),
        ];
    }
}
