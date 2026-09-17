<?php

namespace App\Filament\Pages\Auth;

use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;
use Illuminate\Validation\ValidationException;

class Login extends BaseLogin
{
    public function getHeading(): string|HtmlString
    {
        return 'SISINVENTÁRIO';
    }

    public function getSubheading(): string|HtmlString
    {
        return 'Sistema de Inventários';
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('username')
                    ->label('Matrícula')
                    ->required()
                    ->autocomplete()
                    ->autofocus()
                    ->validationMessages([
                        'required' => 'O campo matrícula é obrigatório',
                    ]),

                TextInput::make('password')
                    ->label('Senha')
                    ->password()
                    ->revealable()
                    ->required()
                    ->validationMessages([
                        'required' => 'O campo senha é obrigatório',
                    ]),

                $this->getRememberFormComponent(),
            ])
            ->statePath('data');
    }

    protected function getCredentialsFromFormData(array $data): array
    {
        return [
            'samaccountname' => $data['username'],
            'password' => $data['password'],
        ];
    }

    protected function throwFailureValidationException(): never
    {
        throw ValidationException::withMessages([
            'data.username' => __('filament-panels::pages/auth/login.messages.failed'),
            'data.password' => __('filament-panels::pages/auth/login.messages.failed'),
        ]);
    }
}
