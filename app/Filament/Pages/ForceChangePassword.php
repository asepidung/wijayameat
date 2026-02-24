<?php

namespace App\Filament\Pages;

use App\Models\User;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Filament\Notifications\Notification;

class ForceChangePassword extends Page
{
    protected ?string $heading = 'Change Your Password!';

    protected static ?string $navigationIcon = 'heroicon-o-key';

    protected static string $view = 'filament.pages.force-change-password';

    protected static bool $shouldRegisterNavigation = false;

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Set New Password')
                    ->description('Please set a new password for your account.')
                    ->schema([
                        TextInput::make('password')
                            ->password()
                            ->required()
                            ->minLength(8)
                            ->confirmed()
                            ->revealable()
                            ->label('New Password'),
                        TextInput::make('password_confirmation')
                            ->password()
                            ->required()
                            ->revealable()
                            ->label('Confirm Password'),
                    ])->columns(1)
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        /** @var User $user */
        $user = Auth::user();

        $user->update([
            'password' => Hash::make($data['password']),
            'must_change_password' => false,
        ]);

        Notification::make()
            ->success()
            ->title('Password Changed Successfully')
            ->send();

        $this->redirect(route('filament.admin.pages.dashboard'));
    }
}
