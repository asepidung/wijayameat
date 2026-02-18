<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CustomerResource\Pages;
use App\Models\Customer;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CustomerResource extends Resource
{
    protected static ?string $model = Customer::class;

    // --- PENGATURAN SIDEBAR (BIAR RAPI DAN GANTENG) ---
    protected static ?string $navigationIcon = 'heroicon-o-users'; // Pakai ikon orang
    protected static ?string $navigationGroup = 'MASTER DATA';    // Masuk grup yang bisa dicolapse
    protected static ?string $navigationLabel = 'Customer';       // Label di menu
    protected static ?int $navigationSort = 2; // Kedua
    // --------------------------------------------------

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // SEKSI 1: IDENTITAS
                Forms\Components\Section::make('Informasi Dasar')
                    ->description('Data utama identitas pelanggan')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nama Cabang/Toko')
                            ->required()
                            ->placeholder('Contoh: AEON Pakansari'),
                        Forms\Components\Select::make('segment_id')
                            ->label('Segmentasi')
                            ->relationship('segment', 'name')
                            ->required()
                            ->preload(),
                        Forms\Components\Select::make('customer_group_id')
                            ->label('Group Customer')
                            ->relationship('customerGroup', 'name')
                            ->required()
                            ->preload()
                            ->helperText('Pilih group untuk menentukan pricelist'),
                    ])->columns(2),

                // SEKSI 2: ALAMAT & KONTAK
                Forms\Components\Section::make('Kontak & Alamat')
                    ->schema([
                        Forms\Components\TextInput::make('email')->email(),
                        Forms\Components\TextInput::make('phone')->label('No. Telepon'),
                        Forms\Components\Textarea::make('address')
                            ->label('Alamat Pengiriman')
                            ->required()
                            ->columnSpanFull(),
                    ])->columns(2),

                // SEKSI 3: OPERASIONAL
                Forms\Components\Section::make('Ketentuan Penagihan & Pengiriman')
                    ->description('Pengaturan tukar faktur dan dokumen wajib')
                    ->schema([
                        Forms\Components\Toggle::make('is_tukar_faktur')
                            ->label('Wajib Tukar Faktur?')
                            ->onIcon('heroicon-m-check')
                            ->offIcon('heroicon-m-x-mark'),
                        Forms\Components\TextInput::make('term_of_payment')
                            ->label('TOP (Hari)')
                            ->numeric()
                            ->default(0)
                            ->suffix('Hari'),
                        Forms\Components\CheckboxList::make('document_requirements')
                            ->label('Kelengkapan Dokumen Wajib')
                            ->options([
                                'po' => 'Purchase Order (PO)',
                                'sj' => 'Surat Jalan (SJ) Kembali',
                                'inv' => 'Invoice Asli',
                                'fp' => 'Faktur Pajak',
                                'sh' => 'Sertifikat Halal',
                            ])
                            ->columns(2),
                        Forms\Components\Textarea::make('notes')
                            ->label('Catatan Khusus Pengiriman')
                            ->placeholder('Contoh: Kirim sebelum jam 7 pagi')
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Toko')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('customerGroup.name')
                    ->label('Group')
                    ->sortable(),
                Tables\Columns\TextColumn::make('segment.name')
                    ->label('Segment'),
                Tables\Columns\IconColumn::make('is_tukar_faktur')
                    ->label('T. Faktur')
                    ->boolean(),
                Tables\Columns\TextColumn::make('term_of_payment')
                    ->label('TOP')
                    ->suffix(' Hari'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCustomers::route('/'),
            'create' => Pages\CreateCustomer::route('/create'),
            'edit' => Pages\EditCustomer::route('/{record}/edit'),
        ];
    }
}
