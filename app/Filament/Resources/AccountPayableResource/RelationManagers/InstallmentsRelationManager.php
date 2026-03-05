<?php

namespace App\Filament\Resources\AccountPayableResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Get;
use Illuminate\Support\Facades\Auth; // <-- JAMU INTELEPHENSE 1

class InstallmentsRelationManager extends RelationManager
{
    protected static string $relationship = 'installments';
    protected static ?string $title = 'Riwayat Cicilan / Pembayaran';

    // ==========================================
    // KUNCI UTAMA: Buka gembok biar tombol bayar muncul di halaman View!
    // ==========================================
    public function isReadOnly(): bool
    {
        return false;
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Detail Transaksi')
                    ->schema([
                        Forms\Components\DatePicker::make('payment_date')
                            ->label('Tgl. Bayar')
                            ->default(now())
                            ->required(),

                        Forms\Components\TextInput::make('amount_paid')
                            ->label('Nominal Transfer (Cash Out)')
                            ->numeric()
                            ->prefix('Rp')
                            ->required()
                            ->live(onBlur: true),

                        Forms\Components\Select::make('payment_method')
                            ->label('Metode Pembayaran')
                            ->options([
                                'BCA' => 'BCA',
                                'MANDIRI' => 'Mandiri',
                                'CASH' => 'Tunai / Kas Kecil',
                            ])->required(),
                    ])->columns(3),

                Forms\Components\Section::make('Penyesuaian (Potongan/Diskon)')
                    ->schema([
                        Forms\Components\TextInput::make('discount_amount')
                            ->label('Diskon / Retur')
                            ->numeric()
                            ->prefix('Rp')
                            ->default(0)
                            ->live(onBlur: true),

                        Forms\Components\TextInput::make('tax_deduction_amount')
                            ->label('Pot. Pajak (PPh 23)')
                            ->numeric()
                            ->prefix('Rp')
                            ->default(0)
                            ->live(onBlur: true),

                        Forms\Components\Placeholder::make('total_debt_reduction_display')
                            ->label('Total Pengurang Hutang')
                            ->content(function (Get $get) {
                                $total = (float)$get('amount_paid') + (float)$get('discount_amount') + (float)$get('tax_deduction_amount');
                                return 'Rp ' . number_format($total, 0, ',', '.');
                            }),
                    ])->columns(3),

                Forms\Components\FileUpload::make('proof_of_payment')
                    ->label('Bukti Transfer / Potong')
                    ->directory('bukti-bayar-ap')
                    ->columnSpanFull(),

                Forms\Components\Textarea::make('note')
                    ->label('Catatan')
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('payment_date')->label('Tgl. Bayar')->date('d-M-Y'),
                Tables\Columns\TextColumn::make('amount_paid')->label('Uang Keluar')->money('IDR'),
                Tables\Columns\TextColumn::make('total_debt_reduction')->label('Total Pengurang')->money('IDR')->weight('bold'),
                Tables\Columns\TextColumn::make('payment_method')->label('Via'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Input Pembayaran')
                    ->icon('heroicon-o-currency-dollar')
                    ->modalHeading('Catat Pembayaran Hutang')
                    ->mutateFormDataUsing(function (array $data) {
                        $data['created_by'] = Auth::id(); // <-- JAMU INTELEPHENSE 2
                        $data['total_debt_reduction'] = (float)($data['amount_paid'] ?? 0) + (float)($data['discount_amount'] ?? 0) + (float)($data['tax_deduction_amount'] ?? 0);
                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->mutateFormDataUsing(function (array $data) {
                        $data['total_debt_reduction'] = (float)($data['amount_paid'] ?? 0) + (float)($data['discount_amount'] ?? 0) + (float)($data['tax_deduction_amount'] ?? 0);
                        return $data;
                    }),
                Tables\Actions\DeleteAction::make(),
            ]);
    }
}
