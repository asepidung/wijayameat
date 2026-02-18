<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SupplierResource\Pages;
use App\Models\Supplier;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SupplierResource extends Resource
{
    protected static ?string $model = Supplier::class;

    protected static ?string $navigationIcon = 'heroicon-o-truck';

    protected static ?string $navigationLabel = 'Data Supplier';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Utama')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nama Supplier')
                            ->required(),
                        Forms\Components\Textarea::make('address')
                            ->label('Alamat Lengkap'),
                        Forms\Components\TextInput::make('term_of_payment')
                            ->label('TOP (Hari)')
                            ->numeric()
                            ->default(0)
                            ->helperText('Isi 0 jika Tunai'),
                    ])->columns(1),

                Forms\Components\Section::make('Kontak & Bank')
                    ->description('Opsional')
                    ->collapsible()
                    ->schema([
                        Forms\Components\TextInput::make('contact_person')->label('Nama PIC'),
                        Forms\Components\TextInput::make('phone')->label('No. Telepon/WA'),
                        Forms\Components\TextInput::make('bank_name')->label('Nama Bank'),
                        Forms\Components\TextInput::make('bank_account_no')->label('No. Rekening'),
                        Forms\Components\TextInput::make('bank_account_name')->label('Atas Nama'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('phone')->label('Telepon'),
                Tables\Columns\TextColumn::make('term_of_payment')
                    ->label('TOP')
                    ->formatStateUsing(fn($state) => $state == 0 ? 'Cash' : $state . ' Hari')
                    ->sortable(),
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

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSuppliers::route('/'),
            'create' => Pages\CreateSupplier::route('/create'),
            'edit' => Pages\EditSupplier::route('/{record}/edit'),
        ];
    }
}
