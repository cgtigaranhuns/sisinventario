<?php

namespace App\Filament\Resources\Inventarios\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class InventariosTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('titulo')
                    ->label('Título do Inventário')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('data_inicio')
                    ->label('Data de Início')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('data_fim')
                    ->label('Data de Fim')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->sortable()
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                Action::make('sync')
                    ->label('Atualizar Bens')
                    ->icon('heroicon-o-arrow-path')
                    ->action(function ($record): void {
                        $record->sincronizarBensConferidos();
                    })
                    ->visible(fn ($record): bool => strtolower(trim((string) $record->status)) === 'concluído')
                    ->requiresConfirmation()
                    ->modalHeading('Atualizar Bens')
                    ->modalDescription('Isso irá atualizar local e situação de todos os bens com base nas conferências registradas neste inventário. Deseja continuar?')
                    ->color('success'),
                EditAction::make()
                    ->label(''),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    //   DeleteBulkAction::make(),
                ]),
            ]);
    }
}