<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UnitResource\Pages;
use App\Filament\Resources\UnitResource\RelationManagers;
use App\Models\Unit;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UnitResource extends Resource
{
    protected static ?string $model = Unit::class;
    protected static ?string $navigationGroup = 'MASTER DATA';
    protected static ?int $navigationSort = 7; // Taruh paling bawah di Master Data

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Master Satuan')
                    ->description('Input satuan berat atau hitungan barang.')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nama Satuan')
                            ->required()
                            ->placeholder('Contoh: Kilogram'),
                        Forms\Components\TextInput::make('symbol')
                            ->label('Simbol')
                            ->required()
                            ->placeholder('Contoh: Kg'),
                    ])->columns(2)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Nama Satuan'),
                Tables\Columns\TextColumn::make('symbol')->label('Simbol'),
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
            'index' => Pages\ListUnits::route('/'),
            'create' => Pages\CreateUnit::route('/create'),
            'edit' => Pages\EditUnit::route('/{record}/edit'),
        ];
    }
}
