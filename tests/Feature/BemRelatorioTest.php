<?php

namespace Tests\Feature;

use App\Models\Bem;
use App\Models\Local;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BemRelatorioTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_download_a_filtered_bem_report_as_pdf(): void
    {
        $local = Local::query()->create(['nome' => 'Depósito Central']);

        Bem::query()->create([
            'rp' => 1001,
            'descricao' => 'Computador',
            'local_id' => $local->id,
            'valor' => 2500.00,
        ]);

        Bem::query()->create([
            'rp' => 1002,
            'descricao' => 'Impressora',
            'local_id' => $local->id,
            'valor' => 800.00,
        ]);

        $response = $this->actingAs(User::factory()->create())
            ->get(route('relatorios.bens.pdf', ['rp' => '1001']));

        $response
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');

        $this->assertMatchesRegularExpression(
            '/^inline; filename="relatorio-bens-\d{8}-\d{6}\.pdf"$/',
            $response->headers->get('content-disposition'),
        );
        $this->assertStringStartsWith('%PDF-', $response->getContent());
    }
}
