<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\ClientCompliance;
use App\Models\Staff;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ImportClientSyncBoardTest extends TestCase
{
    use RefreshDatabase;

    public function test_import_command_fails_if_file_does_not_exist(): void
    {
        $this->artisan('import:client-sync-board', ['--file' => '/non/existent/path.xlsx'])
            ->assertExitCode(1)
            ->expectsOutputToContain('File tidak ditemukan');
    }

    public function test_import_command_imports_data_from_excel(): void
    {
        $filePath = '/Users/user/Downloads/Client Sync Board.xlsx';

        if (!file_exists($filePath)) {
            $this->markTestSkipped('Spreadsheet file is not available at ' . $filePath);
        }

        $this->artisan('import:client-sync-board', [
            '--file' => $filePath,
            '--fresh' => true,
        ])->assertSuccessful();

        $this->assertGreaterThanOrEqual(60, Client::count());
        $this->assertGreaterThanOrEqual(500, ClientCompliance::count());
        $this->assertGreaterThanOrEqual(10, Staff::count());

        // Check specific known client from sheet
        $this->assertDatabaseHas('clients', [
            'name' => 'PT Timur Sulawesi Pangan',
            'client_code' => 'KC001-08',
            'contract_status' => 'Active',
        ]);
    }
}
