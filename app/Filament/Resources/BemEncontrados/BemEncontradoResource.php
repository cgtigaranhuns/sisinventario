<?php

namespace App\Filament\Resources\BemEncontrados;

use App\Filament\Resources\BemEncontrados\Pages\ManageBemEncontrados;
use App\Models\BemEncontrado;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class BemEncontradoResource extends Resource
{
    protected static ?string $model = BemEncontrado::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Cube;

    protected static string|UnitEnum|null $navigationGroup = 'Inventários';

    protected static ?string $label = 'Bens encontrados';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('rp')
                    ->label('RP'),
                TextInput::make('descricao')
                    ->label('Descrição')
                    ->maxLength(255),
                TextInput::make('local')
                    ->label('Local')
                    ->maxLength(255),
                Select::make('situacao')
                    ->label('Situação')
                    ->options([
                        'Servível' => 'Servível',
                        'Inservível' => 'Inservível',
                    ]),
                Hidden::make('encontrado_por_id')
                    ->default(auth()->user()->id),
                Select::make('status')
                    ->visible(fn ($context) => $context === 'edit')
                    ->default('Pendente')
                    ->options([
                        'Pendente' => 'Pendente',
                        'Resolvido' => 'Resolvido',
                    ]),
                FileUpload::make('foto')
                    ->label('Foto')
                    ->image(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('rp')
                    ->label('RP'),
                ImageColumn::make('foto')
                    ->label('Foto')
                    ->circular(),
                TextColumn::make('descricao')
                    ->label('Descrição'),
                TextColumn::make('local')
                    ->label('Local'),
                TextColumn::make('situacao')
                    ->label('Situação')
                    ->sortable()
                    ->searchable()
                    ->badge()
                    ->alignCenter()
                    ->searchable()
                    ->color(fn ($state): string => match ($state) {
                        'Servível' => 'success',
                        'Inservível' => 'danger',
                    })
                    ->icon(fn ($state): string => match ($state) {
                        'Servível' => 'heroicon-o-check-circle',
                        'Inservível' => 'heroicon-o-x-circle',
                    }),
                TextColumn::make('status')
                    ->label('Status')
                    ->sortable()
                    ->searchable()
                    ->badge()
                    ->alignCenter()
                    ->searchable()
                    ->color(fn ($state): string => match ($state) {
                        'Pendente' => 'warning',
                        'Resolvido' => 'success',
                    })
                    ->icon(fn ($state): string => match ($state) {
                        'Pendente' => 'heroicon-o-clock',
                        'Resolvido' => 'heroicon-o-check-circle',
                    }),

                TextColumn::make('encontradoPor.name')
                    ->label('Encontrado por'),

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
            'index' => ManageBemEncontrados::route('/'),
        ];
    }
}
