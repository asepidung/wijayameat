<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RepackResource\Pages;
use App\Models\Repack;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RepackResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = Repack::class;
    protected static ?string $navigationIcon = 'heroicon-o-arrows-right-left';
    protected static ?string $navigationGroup = 'PRODUCTION';
    protected static ?string $navigationLabel = 'Repack';

    /* Permission disamakan dengan standar aplikasi lu */
    public static function getPermissionPrefixes(): array
    {
        return [
            'view_any',
            'view',
            'create',
            'update',
            'delete',
            'lock',
        ];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Repack Document')
                    ->schema([
                        /* Auto Generate No Repack */
                        Forms\Components\TextInput::make('document_no')
                            ->label('No. Proses (Batch)')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->default(function () {
                                $currentYear = date('Y');
                                $prefix = 'RP' . date('y');

                                $count = Repack::withTrashed()->whereYear('repack_date', $currentYear)->count();
                                $sequence = $count + 1;

                                return $prefix . str_pad($sequence, 3, '0', STR_PAD_LEFT);
                            })
                            ->readOnly()
                            ->columnSpan(1),

                        Forms\Components\DatePicker::make('repack_date')
                            ->label('Tgl. Proses')
                            ->required()
                            ->default(now())
                            ->columnSpan(1),

                        Forms\Components\Textarea::make('note')
                            ->label('Catatan / Keterangan')
                            ->columnSpanFull(),

                        Forms\Components\Hidden::make('kunci')
                            ->default(0),

                        Forms\Components\Hidden::make('user_id')
                            ->default(fn() => Auth::id()),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->recordUrl(null)
            ->columns([
                Tables\Columns\TextColumn::make('document_no')
                    ->label('No. Proses')
                    ->searchable()
                    ->weight('bold')
                    ->color('primary'),

                Tables\Columns\TextColumn::make('repack_date')
                    ->label('Tgl. Proses')
                    ->date('d-M-Y')
                    ->sortable(),

                /* Kalkulasi Total Bahan (Input) */
                Tables\Columns\TextColumn::make('total_bahan')
                    ->label('Total Bahan')
                    ->getStateUsing(function (Repack $record) {
                        return DB::table('repack_materials')->where('repack_id', $record->id)->sum('weight');
                    })
                    ->numeric(2)
                    ->suffix(' Kg'),

                /* Kalkulasi Total Hasil (Output) */
                Tables\Columns\TextColumn::make('total_hasil')
                    ->label('Total Hasil')
                    ->getStateUsing(function (Repack $record) {
                        return DB::table('repack_results')
                            ->where('repack_id', $record->id)
                            ->whereNull('deleted_at')
                            ->sum('weight');
                    })
                    ->numeric(2)
                    ->suffix(' Kg'),

                /* Kalkulasi Lost / Balance */
                Tables\Columns\TextColumn::make('lost')
                    ->label('Balance (Lost)')
                    ->getStateUsing(function (Repack $record) {
                        $bahan = DB::table('repack_materials')->where('repack_id', $record->id)->sum('weight');
                        $hasil = DB::table('repack_results')
                            ->where('repack_id', $record->id)
                            ->whereNull('deleted_at')
                            ->sum('weight');
                        return $hasil - $bahan;
                    })
                    ->numeric(2)
                    ->suffix(' Kg')
                    ->badge()
                    ->color(fn($state) => $state < 0 ? 'danger' : 'success'),

                Tables\Columns\TextColumn::make('note')
                    ->label('Catatan')
                    ->limit(30),

                /* Status Kunci */
                Tables\Columns\IconColumn::make('kunci')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-m-lock-closed')
                    ->falseIcon('heroicon-m-lock-open')
                    ->trueColor('danger')
                    ->falseColor('success'),
            ])
            ->actions([
                /* Tombol Lock */
                Tables\Actions\Action::make('toggleLock')
                    ->label(fn(Repack $record) => $record->kunci ? 'Unlock' : 'Lock')
                    ->icon(fn(Repack $record) => $record->kunci ? 'heroicon-m-lock-closed' : 'heroicon-m-lock-open')
                    ->color(fn(Repack $record) => $record->kunci ? 'danger' : 'success')
                    ->iconButton()
                    ->tooltip(fn(Repack $record) => $record->kunci ? 'Buka Kunci' : 'Kunci Repack')
                    ->requiresConfirmation()
                    ->visible(fn() => Auth::user()?->can('lock_repack'))
                    ->action(fn(Repack $record) => $record->update(['kunci' => ! $record->kunci])),

                /* Tombol Input Bahan (Nanti URL-nya kita update setelah halamannya jadi) */
                Tables\Actions\Action::make('input_bahan')
                    ->icon('heroicon-o-archive-box')
                    ->iconButton()
                    ->color('warning')
                    ->tooltip('Input Bahan (Scan)')
                    ->hidden(fn(Repack $record) => $record->kunci == 1),

                /* Tombol Input Hasil (Nanti URL-nya kita update setelah halamannya jadi) */
                Tables\Actions\Action::make('input_hasil')
                    ->icon('heroicon-o-qr-code')
                    ->iconButton()
                    ->color('info')
                    ->tooltip('Input Hasil & Labeling')
                    ->hidden(fn(Repack $record) => $record->kunci == 1),

                Tables\Actions\EditAction::make()
                    ->iconButton()
                    ->hidden(fn(Repack $record) => $record->kunci == 1),

                Tables\Actions\DeleteAction::make()
                    ->iconButton()
                    ->hidden(fn(Repack $record) => $record->kunci == 1),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRepacks::route('/'),
            'create' => Pages\CreateRepack::route('/create'),
            'edit' => Pages\EditRepack::route('/{record}/edit'),
        ];
    }
}
