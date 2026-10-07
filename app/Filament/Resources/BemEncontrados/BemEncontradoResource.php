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
use Filament\Forms\Components\Toggle;
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
                Toggle::make('sem_rp')
                    ->label('Sem RP')
                    ->columnSpanFull()
                    ->live()
                    ->default(false),
                // TODO: Implementar
                TextInput::make('rp')
                    ->visible(fn($get) => !$get('sem_rp'))
                    ->label('RP'),
                TextInput::make('descricao')
                    ->label('Descrição')
                    ->maxLength(255),
                Select::make('local_id')
                    ->label('Local')
                    ->relationship('local', 'nome'),
                Select::make('situacao')
                    ->label('Situação')
                    ->searchable()
                    ->options([
                        'Servível' => 'Servível',
                        'Inservível' => 'Inservível',
                        'Não Localizado' => 'Não Localizado',

                    ]),
                Hidden::make('encontrado_por_id')
                    ->default(auth()->user()->id),
                Select::make('status')
                    ->visible(fn($context) => $context === 'edit')
                    ->default('Pendente')
                    ->options([
                        'Pendente' => 'Pendente',
                        'Resolvido' => 'Resolvido',
                    ]),
                FileUpload::make('foto')
                    ->label('Foto')
                    ->image()
                    ->extraInputAttributes(['capture' => 'environment'])
                    ->automaticallyResizeImagesMode('contain')
                    ->automaticallyResizeImagesToWidth('1280')
                    ->automaticallyResizeImagesToHeight('1280')
                    ->maxSize(10240),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                textColumn::make('sem_rp')
                    ->badge()
                    ->formatStateUsing(fn($state) => $state ? 'Sim' : 'Não')
                    ->color(fn(string $state): string => match ($state) {
                        '1' => 'danger',
                        '0' => 'success',
                    })
                    ->label('Sem RP')
                    ->alignCenter()
                    ->icon(fn(string $state): string => match ($state) {
                        '1' => 'heroicon-o-x-circle',
                        '0' => 'heroicon-o-check-circle',
                    }),


                TextColumn::make('rp')
                    ->label('RP'),
                ImageColumn::make('foto')
                    ->label('Foto')
                    ->circular(),
                TextColumn::make('descricao')
                    ->label('Descrição'),
                TextColumn::make('local.nome')
                    ->label('Local'),
                TextColumn::make('situacao')
                    ->label('Situação')
                    ->sortable()
                    ->searchable()
                    ->badge()
                    ->alignCenter()
                    ->searchable()
                    ->color(fn($state): string => match ($state) {
                        'Servível' => 'success',
                        'Inservível' => 'danger',
                    })
                    ->icon(fn($state): string => match ($state) {
                        'Servível' => 'heroicon-o-check-circle',
                        'Inservível' => 'heroicon-o-x-circle',
                        'Não Localizado' => 'heroicon-o-x-circle',
                    }),
                TextColumn::make('status')
                    ->label('Status')
                    ->sortable()
                    ->searchable()
                    ->badge()
                    ->alignCenter()
                    ->searchable()
                    ->color(fn($state): string => match ($state) {
                        'Pendente' => 'warning',
                        'Resolvido' => 'success',
                    })
                    ->icon(fn($state): string => match ($state) {
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
