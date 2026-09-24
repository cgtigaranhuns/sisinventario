<?php

namespace App\Filament\Resources\Inventarios\RelationManagers;

use App\Models\Bem;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
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
                        'Não Localizado' => 'Não Localizado',                       
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
                            ->get(['id', 'local_id', 'ultima_situacao']);

                        $bens->each(function (Bem $bem) use ($inventario): void {
                            $inventario->conferencias()->create([
                                'rp_id' => $bem->id,
                                'local_id' => $bem->local_id,
                                'situacao' => $bem->ultima_situacao,
                            ]);
                        });

                        Notification::make()
                            ->title("{$bens->count()} bem(ns) adicionado(s)")
                            ->body($bensSemLocal > 0
                                ? "Foram adicionados {$bensSemLocal} bem(ns) sem local."
                                : null)
                            ->success()
                            ->send();
                    }),

                Action::make('addBemAvulso')
                        ->label('Adicionar bem avulso')
                        ->icon('heroicon-o-plus-circle')
                        ->form([
                            Select::make('rp_id')
                                ->label('Bem')
                                ->relationship('bem', 'rp')
                                ->getOptionLabelFromRecordUsing(fn (Bem $record): string => "{$record->rp} - {$record->descricao}")
                                ->searchable(['rp', 'descricao'])
                                ->preload()
                                ->required(),
                        ])
                        ->action(function (array $data): void {
                            $inventario = $this->getOwnerRecord();

                            if ($inventario->conferencias()
                                ->where('rp_id', $data['rp_id'])
                                ->exists()) {
                                Notification::make()
                                    ->title('Bem já adicionado')
                                    ->body('Este bem já foi adicionado a este inventário.')
                                    ->warning()
                                    ->send();

                                return;
                            }

                            $bem = Bem::find($data['rp_id']);

                            if (! $bem) {
                                Notification::make()
                                    ->title('Bem não encontrado')
                                    ->danger()
                                    ->send();

                                return;
                            }

                            $inventario->conferencias()->create([
                                'rp_id' => $bem->id,
                                'local_id' => $bem->local_id,
                                'situacao' => $bem->ultima_situacao,
                            ]);

                            Notification::make()
                                ->title('Bem adicionado com sucesso')
                                ->success()
                                ->send();
                        })
                    

            ])
            ->columns([
                TextColumn::make('bem.rp')
                    ->label('RP')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('bem.descricao')
                    ->label('Descrição')
                    ->limit(30)
                    ->tooltip(fn ($record) => $record->descricao)
                    ->searchable(),
                TextColumn::make('local.nome')
                    ->label('Local')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('situacao')
                    ->label('Situação')
                    ->color(fn (string $state): string => match ($state) {
                        'Servível' => 'success',
                        'Inservível' => 'warning',
                        'Não Localizado' => 'danger',
                        default => 'secondary',
                    })
                    ->badge(),                
                TextColumn::make('conferido_em')
                    ->label('Conferido em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                TextColumn::make('conferidoPor.name')
                    ->limit(15)
                    ->tooltip(fn ($record) => $record->conferidoPor?->name)
                    ->label('Conferido por'),
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
}
