<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;
use Filament\Notifications\Notification;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    // Icon biar keren dikit pake grup orang
    protected static ?string $navigationIcon = 'heroicon-o-users';

    // Biar ngumpul sama menu Roles di satu grup "User Management"
    protected static ?string $navigationGroup = 'User Management';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('User Account Information')
                    ->description('Kelola identitas login dan hak akses staff.')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Full Name')
                            ->placeholder('Nama Lengkap (Muncul di UI)')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('username')
                            ->label('Username')
                            ->placeholder('ID untuk Login (Tanpa Spasi)')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->alpha_dash(),

                        Forms\Components\Select::make('roles')
                            ->label('Assign Roles')
                            ->relationship('roles', 'name')
                            ->multiple()
                            ->preload()
                            ->searchable(),

                        Forms\Components\TextInput::make('password')
                            ->password()
                            ->label('Password')
                            ->placeholder('Kosongkan jika tidak ingin mengubah')
                            // Wajib diisi cuma pas bikin user baru
                            ->required(fn(string $context): bool => $context === 'create')
                            // Jangan simpan kalau inputnya kosong (pas edit)
                            ->dehydrated(fn($state) => filled($state))
                            ->dehydrateStateUsing(fn($state) => Hash::make($state))
                            ->revealable(), // Biar bisa liat password pas ngetik
                    ])->columns(2)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('username')
                    ->label('Username')
                    ->badge()
                    ->color('info')
                    ->searchable(),

                Tables\Columns\TextColumn::make('roles.name')
                    ->label('Roles')
                    ->badge()
                    ->color('success'),

                Tables\Columns\IconColumn::make('must_change_password')
                    ->label('Force Reset?')
                    ->boolean()
                    ->trueIcon('heroicon-o-exclamation-triangle')
                    ->falseIcon('heroicon-o-check-circle')
                    ->color(fn($state) => $state ? 'warning' : 'success'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),

                // TOMBOL SAKTI: Reset ke 1234
                Tables\Actions\Action::make('reset_password')
                    ->label('Reset 1234')
                    ->icon('heroicon-m-arrow-path')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Reset Password Staff?')
                    ->modalDescription('Password akan dikembalikan ke "1234" dan user dipaksa ganti password saat login.')
                    ->action(function (User $record) {
                        $record->update([
                            'password' => Hash::make('1234'),
                            'must_change_password' => true,
                        ]);

                        Notification::make()
                            ->title('Password Berhasil di-Reset!')
                            ->body("Password {$record->name} sekarang adalah: 1234")
                            ->success()
                            ->send();
                    }),
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
