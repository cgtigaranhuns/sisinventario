<?php

namespace App\Filament\Pages;

use App\Jobs\ProcessarImportacaoBens;
use App\Models\User;
use BackedEnum;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;

class ImportarBens extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowUpTray;

    protected static ?string $navigationLabel = 'Importar Bens';

    protected static string|\UnitEnum|null $navigationGroup = 'Operações';

    protected static ?string $title = 'Importar Bens';

    protected static ?string $slug = 'importar-bens';

    protected static ?int $navigationSort = 3;

    protected string $view = 'filament.pages.importar-bens';

    /**
     * @var array<string, mixed>|null
     */
    public ?array $data = [];

    public static function canAccess(): bool
    {
        return Auth::check() && (Auth::user()->hasRole(['TI', 'Administrador']));
    }

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                FileUpload::make('planilha')
                    ->label('Planilha de bens (.xlsx, .xls ou .csv)')
                    ->required()
                    ->acceptedFileTypes([
                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        'application/vnd.ms-excel',
                        'text/csv',
                        'text/plain',
                    ])
                    ->disk('local')
                    ->directory('importacoes-bens')
                    ->visibility('private')
                    ->helperText('A primeira linha deve ser o cabeçalho (é ignorada na importação). Veja a ordem das colunas esperada acima.'),
            ])
            ->statePath('data');
    }

    public function importar(): void
    {
        $data = $this->form->getState();
        

        $caminhoRelativo = $data['planilha'];

        ProcessarImportacaoBens::dispatch($caminhoRelativo, Auth::id());

        Notification::make()
            ->title('Importação iniciada')
            ->body('A planilha está sendo processada em segundo plano. Você será avisado por aqui quando terminar.')
            ->info()
            ->send();

        $this->form->fill();
    }
}