<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LogisticPurchaseOrderResource\Pages;
use App\Filament\Resources\LogisticPurchaseOrderResource\RelationManagers;
use App\Models\LogisticPurchaseOrder;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class LogisticPurchaseOrderResource extends Resource
{
    protected static ?string $model = LogisticPurchaseOrder::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
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
            'index' => Pages\ListLogisticPurchaseOrders::route('/'),
            'create' => Pages\CreateLogisticPurchaseOrder::route('/create'),
            'edit' => Pages\EditLogisticPurchaseOrder::route('/{record}/edit'),
        ];
    }
}
