<?php

namespace App\Console\Commands;

use App\Models\Client;
use App\Models\ClientCompliance;
use App\Models\Project;
use App\Models\ProjectCategory;
use App\Models\ProjectThreat;
use App\Models\Staff;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use XMLReader;
use ZipArchive;

class ImportClientSyncBoard extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:client-sync-board 
                            {--file=/Users/user/Downloads/Client Sync Board.xlsx : Path to Excel file}
                            {--fresh : Clear existing client and compliance records before importing}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import clients, staff, compliances, and projects from Client Sync Board spreadsheet';

    /**
     * Periods column mapping in Mastersheet All Client
     */
    protected const COMPLIANCE_COLUMNS = [
        'Mar 26' => ['21' => 'W',  'uni' => 'X',  'ppn' => 'Y',  '55' => 'Z',  '25' => 'AA', 'lk' => 'AB', 'notes' => 'AC'],
        'Apr 26' => ['21' => 'AD', 'uni' => 'AE', 'ppn' => 'AF', '55' => 'AG', '25' => 'AH', 'lk' => 'AI', 'notes' => 'AJ'],
        'May 26' => ['21' => 'AK', 'uni' => 'AL', 'ppn' => 'AM', '55' => 'AN', '25' => 'AO', 'lk' => 'AP', 'notes' => 'AQ'],
        'Jun 26' => ['21' => 'AR', 'uni' => 'AS', 'ppn' => 'AT', '55' => 'AU', '25' => 'AV', 'lk' => 'AW', 'notes' => 'AX'],
        'Jul 26' => ['21' => 'AY', 'uni' => 'AZ', 'ppn' => 'BA', '55' => 'BB', '25' => 'BC', 'lk' => 'BD', 'notes' => 'BE'],
        'Aug 26' => ['21' => 'BF', 'uni' => 'BG', 'ppn' => 'BH', '55' => 'BI', '25' => 'BJ', 'lk' => 'BK', 'notes' => 'BL'],
        'Sep 26' => ['21' => 'BM', 'uni' => 'BN', 'ppn' => 'BO', '55' => 'BP', '25' => 'BQ', 'lk' => 'BR', 'notes' => 'BS'],
        'Oct 26' => ['21' => 'BT', 'uni' => 'BU', 'ppn' => 'BV', '55' => 'BW', '25' => 'BX', 'lk' => 'BY', 'notes' => 'BZ'],
        'Nov 26' => ['21' => 'CA', 'uni' => 'CB', 'ppn' => 'CC', '55' => 'CD', '25' => 'CE', 'lk' => 'CF', 'notes' => 'CG'],
        'Dec 26' => ['21' => 'CH', 'uni' => 'CI', 'ppn' => 'CJ', '55' => 'CK', '25' => 'CL', 'lk' => 'CM', 'notes' => 'CN'],
    ];

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $filePath = (string) $this->option('file');

        if (!file_exists($filePath)) {
            $this->error("File tidak ditemukan: {$filePath}");
            return Command::FAILURE;
        }

        $this->info("Membuka file spreadsheet: {$filePath}");

        $zip = new ZipArchive();
        if ($zip->open($filePath) !== true) {
            $this->error("Gagal membuka file sebagai arsip zip/xlsx.");
            return Command::FAILURE;
        }

        // 1. Read shared strings
        $sst = $this->extractSharedStrings($zip);
        $this->info("Ekstraksi shared strings selesai: " . count($sst) . " entri.");

        // 2. Read staff from sheet5 (Data Gmail Tim Konsulin)
        $this->syncStaffFromSheet5($zip, $sst);

        // 3. Clear existing if --fresh
        if ($this->option('fresh')) {
            $this->warn("Membersihkan data klien dan kepatuhan lama...");
            ClientCompliance::truncate();
            ProjectThreat::truncate();
            Project::truncate();
            Client::truncate();
        }

        // 4. Read clients from sheet1 (Mastersheet All Client)
        $clientsData = $this->extractClientsFromSheet1($filePath, $sst);
        $zip->close();

        $this->info("Menemukan " . count($clientsData) . " klien dalam Mastersheet.");

        // Ensure default boss user and project categories exist
        $bossUser = User::where('role', 'boss')->first() ?? User::first();
        if (!$bossUser) {
            $bossUser = User::firstOrCreate(
                ['email' => 'boss@konsulin.test'],
                [
                    'name' => 'Dewi Partner',
                    'password' => bcrypt('password'),
                    'role' => 'boss',
                ]
            );
        }
        $taxCategory = ProjectCategory::firstOrCreate(
            ['name' => 'Tax Compliance'],
            ['description' => 'Monthly and annual tax compliance work.', 'is_active' => true]
        );
        $finCategory = ProjectCategory::firstOrCreate(
            ['name' => 'Financial Statement'],
            ['description' => 'Financial report and accounting preparation.', 'is_active' => true]
        );
        $comboCategory = ProjectCategory::firstOrCreate(
            ['name' => 'Finance & Tax'],
            ['description' => 'Combined accounting and tax project.', 'is_active' => true]
        );

        $importedCount = 0;
        $complianceCount = 0;
        $threatCount = 0;

        $bar = $this->output->createProgressBar(count($clientsData));
        $bar->start();

        foreach ($clientsData as $item) {
            $client = Client::updateOrCreate(
                ['name' => $item['name']],
                [
                    'client_code' => $item['client_code'] ?: ('KC' . str_pad((string)($importedCount + 1), 3, '0', STR_PAD_LEFT)),
                    'migration_date' => $item['migration_date'],
                    'client_pic' => $item['client_pic'],
                    'location' => $item['location'],
                    'tax_status' => $item['tax_status'],
                    'business_type' => $item['business_type'],
                    'contract_status' => $item['contract_status'],
                    'start_date' => $item['start_date'],
                    'contract_duration_months' => $item['contract_duration_months'],
                    'end_contract_due_date' => $item['end_contract_due_date'],
                    'client_type' => $item['client_type'],
                    'finance_package' => $item['finance_package'],
                    'tax_package' => $item['tax_package'],
                    'addon' => $item['addon'],
                    'package_detail' => $item['package_detail'],
                    'status' => $item['contract_status'] === 'Active' ? 'active' : 'inactive',
                    'files' => $item['files'],
                    'review_approval' => $item['review_approval'],
                    'tax_pic' => $item['tax_pic'],
                    'accounting_pic' => $item['accounting_pic'],
                ]
            );

            // Save compliance rows
            foreach ($item['compliances'] as $comp) {
                ClientCompliance::updateOrCreate(
                    [
                        'client_id' => $client->id,
                        'period' => $comp['period'],
                    ],
                    [
                        'pph_21' => $comp['pph_21'],
                        'pph_unifikasi' => $comp['pph_unifikasi'],
                        'ppn' => $comp['ppn'],
                        'pp_55' => $comp['pp_55'],
                        'pph_25' => $comp['pph_25'],
                        'lk' => $comp['lk'],
                        'notes' => $comp['notes'],
                    ]
                );
                $complianceCount++;
            }

            // Create or sync project if active
            if ($client->contract_status === 'Active') {
                $category = ($client->finance_package && $client->tax_package) ? $comboCategory : ($client->tax_package ? $taxCategory : $finCategory);
                $serviceType = ($client->finance_package && $client->tax_package) ? 'Finance & Tax' : ($client->tax_package ? 'Tax' : 'Accounting');

                $project = Project::updateOrCreate(
                    [
                        'client_id' => $client->id,
                        'name' => 'Layanan Kepatuhan & Pembukuan 2026 (' . $client->name . ')',
                    ],
                    [
                        'project_category_id' => $category->id,
                        'created_by' => $bossUser?->id ?? 1,
                        'reviewer_id' => $bossUser?->id ?? 1,
                        'service_type' => $serviceType,
                        'status' => 'in_progress',
                        'priority' => 'high',
                        'start_date' => $client->start_date ?? Carbon::create(2026, 1, 1),
                        'due_date' => $client->end_contract_due_date ?? Carbon::create(2026, 12, 31),
                        'description' => $client->package_detail ?: 'Pendampingan kepatuhan perpajakan dan pembukuan akuntansi.',
                    ]
                );

                // Assign staff to project if matched
                $staffIds = [];
                if ($client->tax_pic) {
                    $taxStaff = Staff::where('name', 'like', '%' . explode(' ', trim($client->tax_pic))[0] . '%')->first();
                    if ($taxStaff) {
                        $staffIds[$taxStaff->id] = ['role' => 'pic_tax'];
                    }
                }
                if ($client->accounting_pic) {
                    $accStaff = Staff::where('name', 'like', '%' . explode(' ', trim($client->accounting_pic))[0] . '%')->first();
                    if ($accStaff) {
                        $staffIds[$accStaff->id] = ['role' => 'pic_accounting'];
                    }
                }
                if (!empty($staffIds)) {
                    $project->staff()->sync($staffIds);
                }

                // Check pending notes from compliances to create operational threats
                foreach ($item['compliances'] as $comp) {
                    $note = trim((string)$comp['notes']);
                    if (!empty($note) && !in_array(strtolower($note), ['done', 'ok', '-', 'tepat waktu'])) {
                        ProjectThreat::firstOrCreate(
                            [
                                'project_id' => $project->id,
                                'title' => '[' . $comp['period'] . '] ' . mb_strimwidth($note, 0, 75, '...'),
                            ],
                            [
                                'user_id' => $bossUser?->id ?? 1,
                                'severity' => (stripos($note, 'pending') !== false || stripos($note, 'belum') !== false) ? 'high' : 'medium',
                                'status' => 'open',
                                'description' => "Kendala kepatuhan periode {$comp['period']}: {$note}",
                                'mitigation_plan' => 'Koordinasi segera dengan PIC Klien dan PIC Konsulin terkait pemenuhan data.',
                            ]
                        );
                        $threatCount++;
                    }
                }
            }

            $importedCount++;
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info("Proses impor selesai dengan sukses!");
        $this->table(
            ['Item', 'Jumlah'],
            [
                ['Total Klien Terimpor', $importedCount],
                ['Total Rekod Kepatuhan Bulanan', $complianceCount],
                ['Threats / Hambatan Terdeteksi', $threatCount],
                ['Total Klien Aktif di Sistem', Client::where('contract_status', 'Active')->count()],
                ['Total Klien Nonaktif di Sistem', Client::where('contract_status', '!=', 'Active')->count()],
            ]
        );

        return Command::SUCCESS;
    }

    /**
     * Extract shared strings from zip.
     */
    protected function extractSharedStrings(ZipArchive $zip): array
    {
        $sst = [];
        $xmlContent = $zip->getFromName('xl/sharedStrings.xml');
        if (!$xmlContent) {
            return $sst;
        }

        $sXml = simplexml_load_string($xmlContent);
        foreach ($sXml->si as $si) {
            if (isset($si->t)) {
                $sst[] = (string)$si->t;
            } else {
                $text = '';
                foreach ($si->r as $r) {
                    $text .= (string)$r->t;
                }
                $sst[] = $text;
            }
        }

        return $sst;
    }

    /**
     * Sync staff from sheet 5.
     */
    protected function syncStaffFromSheet5(ZipArchive $zip, array $sst): void
    {
        $xmlContent = $zip->getFromName('xl/worksheets/sheet5.xml');
        if (!$xmlContent) {
            return;
        }

        $sXml = simplexml_load_string($xmlContent);
        foreach ($sXml->sheetData->row as $row) {
            $cells = [];
            foreach ($row->c as $c) {
                $r = (string)$c['r'];
                $col = preg_replace('/[0-9]/', '', $r);
                $val = isset($c->v) ? (string)$c->v : null;
                if ((string)$c['t'] === 's' && $val !== null) {
                    $val = $sst[(int)$val] ?? $val;
                }
                $cells[$col] = $val;
            }

            $name = trim((string)($cells['C'] ?? ''));
            $email = trim((string)($cells['D'] ?? ''));
            $dept = strtolower(trim((string)($cells['E'] ?? 'accounting')));

            if ($name && $email && str_contains($email, '@')) {
                $type = in_array($dept, ['tax', 'accounting', 'legal', 'marketing', 'it']) ? $dept : 'accounting';
                Staff::updateOrCreate(
                    ['email' => $email],
                    [
                        'name' => $name,
                        'phone' => '08' . rand(100000000, 999999999),
                        'type' => $type,
                        'position' => ucfirst($type) . ' Specialist',
                        'is_active' => true,
                    ]
                );
            }
        }

        // Full name PICs from Mastersheet
        $additionalPicNames = [
            ['name' => 'Ni Putu Sintya Bhakti Pratiwi (Sintya)', 'email' => 'sintya@konsulin.test', 'type' => 'tax', 'position' => 'Senior Tax Consultant'],
            ['name' => 'Fitria Kharima Putri (Rima)', 'email' => 'rima@konsulin.test', 'type' => 'tax', 'position' => 'Tax Consultant'],
            ['name' => 'Muhammad Fauzan Romadhona (Fauzan)', 'email' => 'fauzan@konsulin.test', 'type' => 'tax', 'position' => 'Tax Consultant'],
            ['name' => 'Chaerunnisa Hujaji (Chae)', 'email' => 'chae@konsulin.test', 'type' => 'tax', 'position' => 'Tax Consultant'],
            ['name' => 'Ni Putu Meisya Arnita Putri (Nita)', 'email' => 'nita@konsulin.test', 'type' => 'tax', 'position' => 'Junior Tax Consultant'],
            ['name' => 'Made Arvin Ariantara (Arvin)', 'email' => 'arvin@konsulin.test', 'type' => 'accounting', 'position' => 'Senior Accountant'],
            ['name' => 'Choirun Nisa Khurota Ayunin Masruroh (Nisa)', 'email' => 'nisa@konsulin.test', 'type' => 'accounting', 'position' => 'Senior Accountant'],
            ['name' => 'Firdha Chairiyah (Firdha)', 'email' => 'firdha@konsulin.test', 'type' => 'accounting', 'position' => 'Accountant'],
            ['name' => 'Syifa Fauziah (Syifa)', 'email' => 'syifa@konsulin.test', 'type' => 'accounting', 'position' => 'Accountant'],
            ['name' => 'Komang Bagus (Bagus)', 'email' => 'bagus@konsulin.test', 'type' => 'accounting', 'position' => 'Accountant'],
            ['name' => 'Angel Natalia Caroline (Angel)', 'email' => 'angel@konsulin.test', 'type' => 'accounting', 'position' => 'Junior Accountant'],
            ['name' => 'Eri Suryani (Eri)', 'email' => 'eri@konsulin.test', 'type' => 'accounting', 'position' => 'Junior Accountant'],
            ['name' => 'Evi Noviasari (Evi)', 'email' => 'evi@konsulin.test', 'type' => 'accounting', 'position' => 'Junior Accountant'],
            ['name' => 'Muhammad Zamakhoiri Hakim (Hakim)', 'email' => 'hakim@konsulin.test', 'type' => 'accounting', 'position' => 'Junior Accountant'],
            ['name' => 'Susilawati', 'email' => 'susilawati@konsulin.test', 'type' => 'accounting', 'position' => 'Reviewer & Partner'],
            ['name' => 'Adi Putra', 'email' => 'adiputra@konsulin.test', 'type' => 'tax', 'position' => 'Reviewer & Tax Lead'],
        ];

        foreach ($additionalPicNames as $pic) {
            Staff::updateOrCreate(
                ['name' => $pic['name']],
                [
                    'email' => $pic['email'],
                    'phone' => '08' . rand(100000000, 999999999),
                    'type' => $pic['type'],
                    'position' => $pic['position'],
                    'is_active' => true,
                ]
            );
        }

        $this->info("Sinkronisasi data staff PIC perpajakan dan akuntansi selesai.");
    }

    /**
     * Extract client records from sheet1 (Mastersheet All Client).
     */
    protected function extractClientsFromSheet1(string $filePath, array $sst): array
    {
        $reader = new XMLReader();
        $reader->open("zip://{$filePath}#xl/worksheets/sheet1.xml");

        $clients = [];

        while ($reader->read()) {
            if ($reader->nodeType === XMLReader::ELEMENT && $reader->name === 'row') {
                $rIdx = (int)$reader->getAttribute('r');

                if ($rIdx >= 4 && $rIdx <= 75) {
                    $rowXml = $reader->readOuterXml();
                    $xml = simplexml_load_string($rowXml);

                    $cells = [];
                    foreach ($xml->c as $c) {
                        $r = (string)$c['r'];
                        $col = preg_replace('/[0-9]/', '', $r);
                        $t = (string)$c['t'];
                        $val = isset($c->v) ? (string)$c->v : null;
                        if ($t === 's' && $val !== null) {
                            $val = $sst[(int)$val] ?? $val;
                        }
                        $cells[$col] = $val;
                    }

                    $name = trim((string)($cells['C'] ?? ''));
                    if (!empty($name) && $name !== 'PT ABC (XYZ)') {
                        $contractStatus = trim((string)($cells['I'] ?? 'Active'));
                        $dur = (string)($cells['K'] ?? '');
                        $durMonths = str_contains($dur, 'Annual') ? 12 : (str_contains($dur, 'Monthly') ? 1 : (is_numeric($dur) ? (int)$dur : null));

                        $compliances = [];
                        foreach (self::COMPLIANCE_COLUMNS as $period => $colMap) {
                            $compliances[] = [
                                'period' => $period,
                                'pph_21' => trim((string)($cells[$colMap['21']] ?? '-')),
                                'pph_unifikasi' => trim((string)($cells[$colMap['uni']] ?? '-')),
                                'ppn' => trim((string)($cells[$colMap['ppn']] ?? '-')),
                                'pp_55' => trim((string)($cells[$colMap['55']] ?? '-')),
                                'pph_25' => trim((string)($cells[$colMap['25']] ?? '-')),
                                'lk' => trim((string)($cells[$colMap['lk']] ?? '-')),
                                'notes' => trim((string)($cells[$colMap['notes']] ?? '')),
                            ];
                        }

                        $clients[] = [
                            'client_code' => trim((string)($cells['B'] ?? '')),
                            'name' => $name,
                            'migration_date' => $this->convertExcelDate($cells['D'] ?? null),
                            'client_pic' => trim((string)($cells['E'] ?? '')),
                            'location' => trim((string)($cells['F'] ?? '')),
                            'tax_status' => trim((string)($cells['G'] ?? 'PKP')) ?: 'PKP',
                            'business_type' => trim((string)($cells['H'] ?? '')),
                            'contract_status' => $contractStatus ?: 'Active',
                            'start_date' => $this->convertExcelDate($cells['J'] ?? null),
                            'contract_duration_months' => $durMonths,
                            'end_contract_due_date' => $this->convertExcelDate($cells['L'] ?? null),
                            'client_type' => trim((string)($cells['M'] ?? 'Company')) ?: 'Company',
                            'finance_package' => trim((string)($cells['N'] ?? 'LK - A')),
                            'tax_package' => trim((string)($cells['O'] ?? 'SPT Badan')),
                            'addon' => trim((string)($cells['P'] ?? '')),
                            'package_detail' => trim((string)($cells['Q'] ?? '')),
                            'files' => trim((string)($cells['S'] ?? '')),
                            'review_approval' => trim((string)($cells['T'] ?? 'Approved')) ?: 'Approved',
                            'tax_pic' => trim((string)($cells['U'] ?? '')),
                            'accounting_pic' => trim((string)($cells['V'] ?? '')),
                            'compliances' => $compliances,
                        ];
                    }
                }

                if ($rIdx > 75) {
                    break;
                }
            }
        }

        $reader->close();

        return $clients;
    }

    /**
     * Convert Excel serial date to YYYY-MM-DD.
     */
    protected function convertExcelDate($val): ?string
    {
        if ($val === null || $val === '' || $val === '-' || $val === 'Belum') {
            return null;
        }

        if (is_numeric($val)) {
            $f = (float)$val;
            $timestamp = ($f - 25569) * 86400;
            return gmdate('Y-m-d', (int)$timestamp);
        }

        try {
            return Carbon::parse($val)->toDateString();
        } catch (\Exception $e) {
            return null;
        }
    }
}
