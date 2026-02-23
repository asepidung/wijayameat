<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LogisticItemResource\Pages;
use App\Filament\Resources\LogisticItemResource\RelationManagers;
use App\Models\LogisticItem;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class LogisticItemResource extends Resource
{
    protected static ?string $model = LogisticItem::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Logistic Item Information')
                    ->schema([
                        Forms\Components\Select::make('logistic_category_id')
                            ->label('Category')
                            ->relationship('logisticCategory', 'name')
                            ->required()
                            ->preload()
                            ->searchable()
                            // SOP KUNING + UPPERCASE inside
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')
                                    ->required()
                                    ->extraInputAttributes(['style' => 'text-transform:uppercase'])
                                    ->dehydrateStateUsing(fn($state) => strtoupper($state)),
                            ])
                            ->createOptionAction(fn($action) => $action->color('warning')),

                        Forms\Components\TextInput::make('code')
                            ->label('Item Code')
                            ->required()
                            ->unique(ignoreRecord: true),

                        Forms\Components\TextInput::make('name')
                            ->label('Item Name')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->extraInputAttributes(['style' => 'text-transform:uppercase'])
                            ->dehydrateStateUsing(fn($state) => strtoupper($state)),

                        Forms\Components\TextInput::make('unit')
                            ->label('Unit (UOM)')
                            ->placeholder('E.G. PCS / ROLL')
                            ->extraInputAttributes(['style' => 'text-transform:uppercase'])
                            ->dehydrateStateUsing(fn($state) => strtoupper($state)),

                        Forms\Components\Toggle::make('show_in_stock')
                            ->label('Show in Stock List?')
                            ->default(true)
                            ->helperText('Turn OFF for non-inventory items like Office Supplies.')
                            ->columnSpanFull(),
                    ])->columns(2)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')->label('Code')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('name')->label('Item Name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('logisticCategory.name')->label('Category')->badge(),
                Tables\Columns\TextColumn::make('unit')->label('Unit'),
                Tables\Columns\IconColumn::make('show_in_stock')->label('Stockable')->boolean(),
                Tables\Columns\ToggleColumn::make('is_active')->label('Active'),
            ])
            ->recordUrl(null)
            ->recordAction('view')
            ->actions([
                Tables\Actions\ViewAction::make()->extraAttributes(['style' => 'display: none !important;']),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([])
            ->defaultSort('name', 'asc');
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
            'index' => Pages\ListLogisticItems::route('/'),
            'create' => Pages\CreateLogisticItem::route('/create'),
            'edit' => Pages\EditLogisticItem::route('/{record}/edit'),
        ];
    }
}
