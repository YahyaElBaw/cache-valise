<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DemoDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_and_reports_render_and_export_pdf_and_csv(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $this->seed(DemoDataSeeder::class);

        $this->get(route('dashboard'))->assertOk();

        foreach (['charges', 'sales', 'margin', 'receivables', 'debts', 'cash'] as $type) {
            $this->get(route('reports.show', ['type' => $type]))->assertOk();

            $csv = $this->get(route('reports.show', ['type' => $type, 'format' => 'csv']));
            $csv->assertOk();
            $this->assertStringContainsString('text/csv', (string) $csv->headers->get('content-type'));

            $pdf = $this->get(route('reports.show', ['type' => $type, 'format' => 'pdf']));
            $pdf->assertOk();
            $this->assertStringContainsString('application/pdf', (string) $pdf->headers->get('content-type'));
        }
    }
}
