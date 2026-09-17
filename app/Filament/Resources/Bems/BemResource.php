<?php

namespace App\Filament\Resources\Bems;

use App\Filament\Resources\Bems\Pages\ManageBems;
use App\Models\Bem;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class BemResource extends Resource
{
    protected static ?string $model = Bem::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Cube;

    protected static ?string $navigationLabel = 'Bens';

    protected static string|\UnitEnum|null $navigationGroup = 'Cadastros';

    protected static ?string $title = 'Bens';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('rp')
                    ->label('RP')
                    ->required()
                    ->maxLength(255),
                TextInput::make('descricao')
                    ->label('Descrição')
                    ->required()
                    ->maxLength(255),
                Select::make('local_id')
                    ->label('Local')
                    ->relationship('local', 'nome')
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('ultima_situacao')
                    ->label('Última Situação')
                    ->options([
                        'Servível' => 'Servível',
                        'Inservível' => 'Inservível',
                    ]),
                TextInput::make('elemento_despesa')
                    ->label('Elemento de Despesa')
                    ->maxLength(255),
                TextInput::make('valor')
                    ->label('Valor')
                    ->numeric()
                    ->maxLength(255),
                Textarea::make('observacao')
                    ->label('Observação')
                    ->maxLength(65535),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('rp')
                    ->label('RP')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('descricao')
                    ->label('Descrição')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('local.nome')
                    ->label('Local')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('ultima_situacao')
                    ->label('Última Situação')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Servível' => 'success',
                        'Inservível' => 'danger',
                        default => 'secondary',
                    })
                    ->sortable()
                    ->searchable(),
                TextColumn::make('elemento_despesa')
                    ->label('Elemento de Despesa')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('valor')
                    ->label('Valor')
                    ->money('BRL', true)
                    ->summarize(Sum::make()->label('Total')->money('BRL', true))
                    ->sortable()
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
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
            'index' => ManageBems::route('/'),
        ];
    }
}
