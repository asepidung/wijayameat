<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AccountPayableResource\Pages;
use App\Filament\Resources\AccountPayableResource\RelationManagers;
use App\Models\AccountPayable;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AccountPayableResource extends Resource
{
    protected static ?string $model = AccountPayable::class;
    protected static ?string $navigationIcon = 'heroicon-o-credit-card';
    protected static ?string $navigationGroup = 'FINANCE';
    protected static ?string $navigationLabel = 'Account Payable';
    protected static ?int $navigationSort = 19;

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('due_date', 'asc')
            ->recordAction(Tables\Actions\ViewAction::class)
            ->columns([
                Tables\Columns\TextColumn::make('payable.po_number')
                    ->label('No. PO')
                    ->searchable()
                    ->sortable()
                    ->description(fn($record): string => "Ref: " . match ($record->payable_type) {
                        'App\Models\LogisticPurchaseOrder' => 'PO Logistic',
                        'App\Models\BeefPurchaseOrder' => 'PO Beef',
                        'App\Models\CattlePurchaseOrder' => 'PO Cattle',
                        default => str_replace('App\Models\\', '', $record->payable_type),
                    }),

                Tables\Columns\TextColumn::make('supplier.name')
                    ->label('Supplier')
                    ->searchable(),

                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Total Hutang')
                    ->money('IDR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('balance_due')
                    ->label('Sisa Hutang')
                    ->money('IDR')
                    ->weight('bold')
                    ->color(fn($state) => $state > 0 ? 'danger' : 'success'),

                Tables\Columns\TextColumn::make('due_date')
                    ->label('Jatuh Tempo')
                    ->date('d-M-Y')
                    ->sortable()
                    ->color(fn($record) => ($record->due_date < now() && $record->balance_due > 0) ? 'danger' : 'gray'),

                // INI OBATNYA BRO! Pengganti BadgeColumn di Filament v3
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'UNPAID' => 'danger',
                        'PARTIAL' => 'warning',
                        'PAID' => 'success',
                        default => 'gray',
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()->extraAttributes(['class' => 'hidden']),
            ])
            ->bulkActions([]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\InstallmentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAccountPayables::route('/'),
            'view' => Pages\ViewAccountPayable::route('/{record}'),
        ];
    }
}
