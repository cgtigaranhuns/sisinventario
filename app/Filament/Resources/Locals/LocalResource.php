<?php

namespace App\Filament\Resources\Locals;

use App\Filament\Resources\Locals\Pages\ManageLocals;
use App\Models\Local;
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

class LocalResource extends Resource
{
    protected static ?string $model = Local::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::BuildingLibrary;

    protected static ?string $navigationLabel = 'Locais';

    protected static string|\UnitEnum|null $navigationGroup = 'Cadastros';

    protected static ?string $title = 'Locais';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nome')
                    ->label('Nome do Local')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                TextInput::make('descricao')
                    ->label('Descrição')
                    ->maxLength(255),
                TextInput::make('responsavel')
                    ->label('Responsável')
                    ->maxLength(255),
                TextInput::make('uorg')
                    ->label('UORG')
                    ->maxLength(255),
                TextInput::make('setor')
                    ->label('Setor'),
                Select::make('bloco')
                    ->options([
                        'Bloco B' => 'Blobo B',
                        'Bloco C' => 'Bloco C',
                        'Bloco D' => 'Blobo D',
                        'Bloco E' => 'Bloco E',
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nome')
                    ->label('Nome do Local')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('setor')
                    ->label('Setor')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('bloco')
                    ->label('Bloco')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('uorg')
                    ->label('UORG')
                    ->searchable()
                    ->sortable(),

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
            'index' => ManageLocals::route('/'),
        ];
    }
}
