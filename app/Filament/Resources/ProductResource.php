<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Set;
use Filament\Forms\Get;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    // Ikonnya kita ganti biar keren (pake box/produk)
    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    // INI YANG BIKIN DIA MUNCUL DI SIDEBAR
    protected static ?string $navigationGroup = 'MASTER DATA';

    // Kita taruh di urutan ke-6 (setelah Supplier, Customer, Grup, Segment, Category)
    protected static ?int $navigationSort = 6;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Produk')
                    ->description('Pilih jenis struktur barang untuk menentukan penomoran otomatis.')
                    ->aside()
                    ->schema([
                        // 1. Pilih Struktur
                        Forms\Components\Select::make('structure_type')
                            ->label('Struktur Barang')
                            ->options([
                                'main' => 'Barang Utama (Induk)',
                                'sub' => 'Barang Turunan (Varian/TS)',
                            ])
                            ->required()
                            ->live()
                            ->afterStateUpdated(function (Set $set) {
                                $set('code', null);
                                $set('parent_id', null);
                            }),

                        // 2. Jika Turunan: Pilih Induknya
                        Forms\Components\Select::make('parent_id')
                            ->label('Barang Induk')
                            ->relationship('parent', 'name', fn($query) => $query->whereNull('parent_id'))
                            ->searchable()
                            ->preload()
                            ->visible(fn(Get $get) => $get('structure_type') === 'sub')
                            ->required(fn(Get $get) => $get('structure_type') === 'sub') // Wajib diisi kalau turunan
                            ->live()
                            ->afterStateUpdated(function ($state, Set $set) {
                                if (!$state) return;
                                $parent = \App\Models\Product::find($state);

                                $childCount = \App\Models\Product::where('parent_id', $state)->count();
                                $newSuffix = str_pad($childCount + 1, 2, '0', STR_PAD_LEFT);

                                $set('code', substr($parent->code, 0, 4) . $newSuffix);

                                // DISINI KUNCINYA: Ngisi kategori bapaknya ke kategori anak
                                $set('category_id', $parent->category_id);
                            }),

                        // 3. Kategori (Sekarang kita buat Disabled bukan Hidden)
                        Forms\Components\Select::make('category_id')
                            ->label('Kategori (Cut)')
                            ->relationship('category', 'name')
                            ->required()
                            ->live()
                            // Kalau turunan, dia otomatis ngikut bapaknya & nggak bisa diubah
                            ->disabled(fn(Get $get) => $get('structure_type') === 'sub')
                            // Wajib pake dehydrated(true) agar nilai yang di-disabled tetep dikirim ke database!
                            ->dehydrated(true)
                            ->afterStateUpdated(function ($state, Set $set) {
                                if (!$state) return;
                                $prefix = \App\Models\Category::find($state)->prefix;

                                $lastItem = \App\Models\Product::where('category_id', $state)
                                    ->whereNull('parent_id')
                                    ->orderBy('code', 'desc')
                                    ->first();

                                $nextSeq = $lastItem ? ((int)substr($lastItem->code, 1, 3) + 1) : 1;
                                $set('code', $prefix . str_pad($nextSeq, 3, '0', STR_PAD_LEFT) . '00');
                            }),

                        Forms\Components\TextInput::make('name')
                            ->label('Nama Barang')
                            ->required()
                            ->placeholder('Contoh: TENDERLOIN TS')
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('code')
                            ->label('Kode Barang (Otomatis)')
                            ->readOnly()
                            ->required(),

                        Forms\Components\Select::make('unit_id')
                            ->label('Satuan')
                            ->relationship('unit', 'name')
                            ->default(1)
                            ->required(),
                    ])->columns(2)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->label('Kode')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Barang')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Kategori'),
                Tables\Columns\TextColumn::make('stock')
                    ->label('Stok')
                    ->suffix(' Kg'),
                Tables\Columns\ToggleColumn::make('is_active')
                    ->label('Status'),
            ])
            ->defaultSort('code', 'asc')
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->relationship('category', 'name'),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
