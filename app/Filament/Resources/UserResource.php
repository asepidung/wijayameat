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
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;

class UserResource extends Resource
{
    protected static ?string $model = User::class;
    protected static ?int $navigationSort = 1;
    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationGroup = 'USERS';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Identitas User')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Full Name')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('username')
                            ->label('Username')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->rules(['alpha_dash'])
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($state, callable $set) {
                                if (filled($state)) {
                                    $set('email', strtolower($state) . '@wijayameat.co.id');
                                }
                            }),

                        Forms\Components\TextInput::make('email')
                            ->label('Email Address')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->readOnly(),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Akun Aktif')
                            ->default(true)
                            ->inline(false)
                            ->helperText('Matikan jika karyawan resign/nonaktif.'),

                        Forms\Components\TextInput::make('password')
                            ->label('Password')
                            ->password()
                            ->default('1234')
                            ->required(fn(string $context): bool => $context === 'create')
                            ->dehydrated(fn($state) => filled($state))
                            ->dehydrateStateUsing(fn($state) => Hash::make($state))
                            ->revealable(),

                        Forms\Components\Hidden::make('must_change_password')
                            ->default(true),

                        /* Grup Jabatan Utama (Cukup 2 Role seperti yang lu mau) */
                        Forms\Components\CheckboxList::make('roles')
                            ->label('Role Utama')
                            ->relationship('roles', 'name')
                            ->columns(2)
                            ->columnSpanFull(),
                    ])->columns(2),

                /* Ini dia Matriks Spesifik per Modul yang lu minta */
                Forms\Components\Section::make('Hak Akses Ekstra (Direct Permissions)')
                    ->description('Atur hak akses spesifik untuk user ini tanpa harus membuat Role baru.')
                    ->schema(static::getPermissionMatrix())
                    ->columns(1),
            ]);
    }

    /**
     * Fungsi ajaib buat ngebangun Matriks Permission secara dinamis dan SANGAT RAPI
     */
    public static function getPermissionMatrix(): array
    {
        $permissions = \Spatie\Permission\Models\Permission::all();

        // Definisikan urutan persis seperti yang kita butuhkan sekarang
        $orderedActions = [
            'view',
            'view_any',
            'create',
            'update',
            'delete',
            'review', // Custom actions taruh di paling belakang
            'approve',
        ];

        // Sortir action dari string terpanjang ke terpendek agar pencocokan nama akurat
        $searchActions = array_merge([], $orderedActions);
        usort($searchActions, fn($a, $b) => strlen($b) <=> strlen($a));

        // 2. Kelompokkan permission berdasarkan nama Entitas/Modul
        $groupedPermissions = [];
        foreach ($permissions as $permission) {
            $name = $permission->name;
            $actionMatch = null;
            $entityMatch = null;

            foreach ($searchActions as $action) {
                if (\Illuminate\Support\Str::startsWith($name, $action . '_')) {
                    $actionMatch = $action;
                    $entityMatch = substr($name, strlen($action) + 1);
                    break;
                }
            }

            if ($actionMatch && $entityMatch) {
                $entityName = \Illuminate\Support\Str::headline($entityMatch);
                $groupedPermissions[$entityName][] = [
                    'name' => $permission->name,
                    'action' => $actionMatch,
                    'label' => \Illuminate\Support\Str::headline($actionMatch),
                ];
            } else {
                $groupedPermissions['Custom Permissions'][] = [
                    'name' => $permission->name,
                    'action' => $name,
                    'label' => \Illuminate\Support\Str::headline($name),
                ];
            }
        }

        // 3. Bangun UI UI Section per entitas biar persis kayak Shield
        $schema = [];
        foreach ($groupedPermissions as $entity => $perms) {
            // Urutkan ulang checkbox sesuai dengan format $orderedActions
            usort($perms, function ($a, $b) use ($orderedActions) {
                $posA = array_search($a['action'], $orderedActions);
                $posB = array_search($b['action'], $orderedActions);
                if ($posA === false) $posA = 999;
                if ($posB === false) $posB = 999;
                return $posA <=> $posB;
            });

            $options = [];
            foreach ($perms as $perm) {
                $options[$perm['name']] = $perm['label'];
            }

            $schema[] = Forms\Components\Section::make($entity)
                ->schema([
                    Forms\Components\CheckboxList::make('custom_permissions_' . \Illuminate\Support\Str::slug($entity))
                        ->hiddenLabel()
                        ->options($options)
                        ->columns(4) // 4 kolom persis kayak contoh OK
                        ->bulkToggleable()
                        ->afterStateHydrated(function ($component, $record) use ($options) {
                            if ($record) {
                                $hasPerms = $record->permissions()
                                    ->whereIn('name', array_keys($options))
                                    ->pluck('name')
                                    ->toArray();
                                $component->state($hasPerms);
                            }
                        })
                ])
                ->collapsible() // Biar bisa dibuka-tutup pakai panah kayak bawaan Shield
                ->compact();    // Hilangkan jarak padding yang terlalu lebar
        }

        return $schema;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),

                Tables\Columns\TextColumn::make('username')->badge()->color('info')->searchable(),

                Tables\Columns\TextColumn::make('roles.name')->label('Roles')->badge()->color('success'),

                Tables\Columns\IconColumn::make('must_change_password')
                    ->label('Force Reset?')
                    ->boolean()
                    ->trueIcon('heroicon-o-exclamation-triangle')
                    ->falseIcon('heroicon-o-check-circle')
                    ->color(fn($state) => $state ? 'warning' : 'success'),

                // Posisinya harus di DALAM sini ya, Bro
                Tables\Columns\ToggleColumn::make('is_active')
                    ->label('Active')
                    ->sortable(),
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
