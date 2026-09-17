<?php

namespace App\Filament\Resources\Users;

use App\Filament\Resources\Users\Pages\ManageUsers;
use App\Models\Local;
use App\Models\User;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::User;

    protected static ?string $navigationLabel = 'Usuários';

    protected static string|\UnitEnum|null $navigationGroup = 'Segurança';

    protected static ?string $title = 'Usuários';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nome')
                    ->required()
                    ->maxLength(255),
                TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->required()
                    ->maxLength(255),
                TextInput::make('username')
                    ->label('Matrícula')
                    ->required()
                    ->maxLength(255),
                Select::make('local_id')
                    ->label('Local')
                    ->options(Local::query()->pluck('nome', 'id')->toArray())
                    ->searchable()
                    ->multiple()
                    ->preload()
                    ->required(),
                Select::make('roles')
                    ->label('Perfil')                    
                    ->preload()
                    ->relationship('roles', 'name'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nome')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('email')
                    ->label('Email')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('username')
                    ->label('Matrícula')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('local_id')
                    ->label('Local')
                    ->formatStateUsing(function ($state) {
                        $ids = is_array($state) ? $state : [$state];

                        return collect($ids)
                            ->filter(fn($id) => filled($id))
                            ->map(fn($id) => Local::find($id)?->nome ?? $id)
                            ->implode(', ');
                    })
                    ->sortable()
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make()
                    ->label(''),
                DeleteAction::make()
                    ->label(''),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageUsers::route('/'),
        ];
    }
}
