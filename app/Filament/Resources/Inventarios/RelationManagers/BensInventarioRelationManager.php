<?php

namespace App\Filament\Resources\Inventarios\RelationManagers;

use App\Models\Bem;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class BensInventarioRelationManager extends RelationManager
{
    protected static string $relationship = 'conferencias';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('rp_id')
                    ->label('Bem')
                    ->relationship('bem', 'rp')
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('local_id')
                    ->label('Local')
                    ->relationship('local', 'nome')
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('situacao')
                    ->label('Situação')
                    ->options([
                        'Servível' => 'Servível',
                        'Inservível' => 'Inservível',
                        'Ocioso' => 'Ocioso',
                        'Recuperável' => 'Recuperável',
                        'Antieconômico' => 'Antieconômico',
                        'Irrecuperável' => 'Irrecuperável',
                        'Não localizado' => 'Não localizado',
                    ]),
                Textarea::make('observacao')
                    ->label('Observação'),
                DateTimePicker::make('conferido_em')
                    ->label('Conferido em'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->headerActions([
                Action::make('adicionarTodosBens')
                    ->label('Adicionar todos os bens')
                    ->icon('heroicon-o-plus')
                    ->requiresConfirmation()
                    ->action(function (): void {
                        $inventario = $this->getOwnerRecord();
                        $idsJaAdicionados = $inventario->conferencias()->pluck('rp_id');
                        $bensSemLocal = Bem::query()
                            ->whereNotIn('id', $idsJaAdicionados)
                            ->whereNull('local_id')
                            ->count();
                        $bens = Bem::query()
                            ->whereNotIn('id', $idsJaAdicionados)
                           // ->whereNotNull('local_id')
                            ->get(['id', 'local_id']);

                        $bens->each(fn (Bem $bem) => $inventario->conferencias()->create([
                            'rp_id' => $bem->id,
                            'local_id' => $bem->local_id,
                        ]));

                        Notification::make()
                            ->title("{$bens->count()} bem(ns) adicionado(s)")
                            ->body($bensSemLocal > 0
                                ? "Foram adicionados {$bensSemLocal} bem(ns) sem local."
                                : null)
                            ->success()
                            ->send();
                    }),

            ])
            ->columns([
                TextColumn::make('bem.rp')
                    ->label('RP')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('bem.descricao')
                    ->label('Descrição')
                    ->limit(40)
                    ->searchable(),
                TextColumn::make('local.nome')
                    ->label('Local')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('situacao')
                    ->label('Situação')
                    ->badge(),
                TextColumn::make('observacao')
                    ->label('Observação')
                    ->limit(40),
                TextColumn::make('conferido_em')
                    ->label('Conferido em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                TextColumn::make('conferido_por_id')
                    ->label('Conferido por'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
