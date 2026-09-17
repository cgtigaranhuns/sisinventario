<?php

namespace App\Filament\Resources\Inventarios\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class InventarioForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informações do Inventário')
                    ->columnSpanFull()
                    ->schema([
                        Grid::make()
                            ->columnSpan(3)
                            ->columns(3)
                            ->schema([
                                TextInput::make('titulo')
                                    ->label('Título do Inventário')
                                    ->required()
                                    ->columnSpan([
                                        'sm' => 1,
                                        'md' => 3,
                                        'lg' => 3,
                                    ])
                                    ->maxLength(255),
                                DatePicker::make('data_inicio')
                                    ->label('Data de Início')
                                    ->nullable(),
                                DatePicker::make('data_fim')
                                    ->label('Data de Fim')
                                    ->nullable(),
                                ToggleButtons::make('status')
                                    ->label('Status')
                                    ->inline()
                                    ->options([
                                        'Cadastrado' => 'Cadastrado',
                                        'Em andamento' => 'Em andamento',
                                        'Concluído' => 'Concluído',
                                    ])
                                    ->default('Cadastrado')
                                    ->colors([
                                        'Cadastrado' => 'warning',
                                        'Em andamento' => 'danger',
                                        'Concluído' => 'success',
                                    ])
                                    ->required(),
                            ]),

                        Repeater::make('comissao')
                            ->label('Comissão')
                            ->schema([
                                Grid::make()
                                    ->schema([
                                        TextInput::make('nome')
                                            ->label('Nome do Membro')
                                            ->required()
                                            ->maxLength(255),
                                        Select::make('funcao')
                                            ->label('Função na Comissão')
                                            ->options([
                                                'Presidente' => 'Presidente',
                                                'Membro' => 'Membro',
                                            ])
                                            ->required(),
                                    ])
                                    ->columns(2),
                            ])
                            ->columnSpanFull()
                            ->collapsible()
                            ->defaultItems(1)
                            ->minItems(1)
                            ->createItemButtonLabel('Adicionar Membro'),

                    ]),
            ]);
    }
}
