<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CustomerGroupResource\Pages;
use App\Models\CustomerGroup;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CustomerGroupResource extends Resource
{
    protected static ?string $model = CustomerGroup::class;

    // --- PENGATURAN SIDEBAR ---
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack'; // Ikon Tumpukan
    protected static ?string $navigationGroup = 'MASTER DATA'; // Gabung sama Segment
    protected static ?string $navigationLabel = 'Group Customer';
    protected static ?string $modelLabel = 'Group Customer';
    // --------------------------

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Data Group Customer')
                    ->description('Grup ini digunakan untuk pengelompokan harga (Pricelist)')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nama Group')
                            ->placeholder('Contoh: AEON, ABUBA, RETAIL UMUM')
                            ->required()
                            ->unique(ignoreRecord: true),
                        Forms\Components\Textarea::make('description')
                            ->label('Keterangan')
                            ->placeholder('Catatan tambahan jika ada...'),
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Group')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('description')
                    ->label('Keterangan')
                    ->limit(50),
            ])
            ->filters([])
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
            'index' => Pages\ListCustomerGroups::route('/'),
            'create' => Pages\CreateCustomerGroup::route('/create'),
            'edit' => Pages\EditCustomerGroup::route('/{record}/edit'),
        ];
    }
}
