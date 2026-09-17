<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\ClientCompliance;
use App\Models\Project;
use App\Models\ProjectCategory;
use App\Models\ProjectProgressUpdate;
use App\Models\ProjectTask;
use App\Models\ProjectThreat;
use App\Models\Staff;
use App\Models\User;
use App\Models\WebsiteContent;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        foreach ([
            ['key' => 'hero', 'label' => 'Homepage Hero', 'type' => 'hero', 'title' => 'Sistem Manajemen Proyek & Operasional Konsultasi.', 'body' => 'Konsulin Manager adalah platform internal terintegrasi untuk mengelola kepatuhan pajak berkala, laporan akuntansi, audit risiko proyek, dan pelacakan waktu kerja konsultan.', 'button_text' => 'Masuk ke Workspace', 'button_url' => '/login', 'sort_order' => 10],
            ['key' => 'services', 'label' => 'Services Intro', 'type' => 'section', 'title' => 'Fungsi & Arsitektur Sistem.', 'body' => 'Dirancang dengan presisi gaya Jira untuk menyelaraskan alur kerja antara Partner (Admin), Supervisor (Reviewer), dan Pelaksana (Staff).', 'sort_order' => 20],
            ['key' => 'contact', 'label' => 'Contact CTA', 'type' => 'cta', 'title' => 'Akses Workspace Konsulin Manager', 'body' => 'Gunakan kredensial resmi kantor atau akun demo untuk mengevaluasi fitur manajemen proyek sesuai peran Anda.', 'button_text' => 'Buka Halaman Login', 'button_url' => '/login', 'sort_order' => 30],
        ] as $content) {
            WebsiteContent::updateOrCreate(['key' => $content['key']], $content);
        }

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'view projects',
            'create projects',
            'edit projects',
            'delete projects',
            'view clients',
            'create clients',
            'edit clients',
            'delete clients',
            'manage tasks',
            'update task progress',
            'manage threats',
            'manage staff',
            'manage categories',
            'manage website-content',
        ];

        foreach ($permissions as $permission) {
            \Spatie\Permission\Models\Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Roles: admin, reviewer, staff (with backward compatibility for boss and employee)
        $adminRole = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $adminRole->syncPermissions(\Spatie\Permission\Models\Permission::all());

        $reviewerRole = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'reviewer', 'guard_name' => 'web']);
        $reviewerRole->syncPermissions([
            'view projects',
            'edit projects',
            'view clients',
            'manage tasks',
            'update task progress',
            'manage threats',
        ]);

        $staffRole = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'staff', 'guard_name' => 'web']);
        $staffRole->syncPermissions([
            'view projects',
            'update task progress',
        ]);

        $bossRole = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'boss', 'guard_name' => 'web']);
        $bossRole->syncPermissions(\Spatie\Permission\Models\Permission::all());

        $employeeRole = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'employee', 'guard_name' => 'web']);
        $employeeRole->syncPermissions([
            'view projects',
            'create projects',
            'edit projects',
            'view clients',
            'create clients',
            'edit clients',
            'manage tasks',
            'update task progress',
            'manage threats',
        ]);

        $boss = User::updateOrCreate(
            ['email' => 'boss@konsulin.test'],
            [
                'name' => 'Dewi Partner',
                'password' => \Illuminate\Support\Facades\Hash::make('password'),
                'role' => 'admin',
            ]
        );
        $boss->assignRole([$adminRole, $bossRole]);

        $adminUser = User::updateOrCreate(
            ['email' => 'admin@konsulin.test'],
            [
                'name' => 'Dewi Admin',
                'password' => \Illuminate\Support\Facades\Hash::make('password'),
                'role' => 'admin',
            ]
        );
        $adminUser->assignRole([$adminRole, $bossRole]);

        $reviewer = User::updateOrCreate(
            ['email' => 'reviewer@konsulin.test'],
            [
                'name' => 'Nadia Reviewer',
                'password' => \Illuminate\Support\Facades\Hash::make('password'),
                'role' => 'reviewer',
            ]
        );
        $reviewer->assignRole($reviewerRole);

        $employees = collect([
            ['name' => 'Nadia Consultant', 'email' => 'nadia@konsulin.test', 'role' => 'reviewer', 'roleObj' => $reviewerRole],
            ['name' => 'Rafi Staff', 'email' => 'rafi@konsulin.test', 'role' => 'staff', 'roleObj' => $staffRole],
            ['name' => 'Bagus Accountant', 'email' => 'bagus@konsulin.test', 'role' => 'staff', 'roleObj' => $staffRole],
        ])->map(function (array $user) use ($employeeRole) {
            $roleObj = $user['roleObj'];
            unset($user['roleObj']);
            $u = User::updateOrCreate(
                ['email' => $user['email']],
                $user + [
                    'password' => \Illuminate\Support\Facades\Hash::make('password'),
                ]
            );
            $u->assignRole([$roleObj, $employeeRole]);
            return $u;
        });

        $clients = collect([
            [
                'client_code' => 'CLI-2026-001',
                'name' => 'PT Sinar Pajak Utama',
                'email' => 'finance@sinar.test',
                'phone' => '08123456789',
                'tax_id' => '12.345.678.9-012.000',
                'migration_date' => '2026-01-15',
                'client_pic' => 'Budi Santoso (Direktur Keuangan)',
                'location' => 'Jakarta Selatan, DKI Jakarta',
                'tax_status' => 'PKP',
                'business_type' => 'Jasa Konsultasi Bisnis & IT',
                'contract_status' => 'Active',
                'start_date' => '2026-01-01',
                'contract_duration_months' => 12,
                'end_contract_due_date' => '2026-12-31',
                'client_type' => 'Badan (PT)',
                'finance_package' => 'Premium Financial Reporting',
                'tax_package' => 'All-in Monthly Tax Compliance',
                'addon' => 'SPT Tahunan Badan & Audit Support',
                'package_detail' => 'Layanan kepatuhan bulanan PPh 21, PPh Unifikasi, PPN, rekonsiliasi GL & closing laporan keuangan bulanan.',
                'status' => 'active',
                'files' => 'https://drive.google.com/drive/folders/konsulin-sinar-pajak',
                'review_approval' => 'Approved',
                'tax_pic' => 'Nadia Tax Consultant',
                'accounting_pic' => 'Ari Senior Accountant',
            ],
            [
                'client_code' => 'CLI-2026-002',
                'name' => 'CV Akuntansi Maju Mandiri',
                'email' => 'owner@maju.test',
                'phone' => '0822222222',
                'tax_id' => '98.765.432.1-000.000',
                'migration_date' => '2026-02-01',
                'client_pic' => 'Ibu Maya (Managing Partner)',
                'location' => 'Bandung, Jawa Barat',
                'tax_status' => 'Non-PKP',
                'business_type' => 'Perdagangan Retail & Grosir',
                'contract_status' => 'Active',
                'start_date' => '2026-02-01',
                'contract_duration_months' => 6,
                'end_contract_due_date' => '2026-07-31',
                'client_type' => 'Badan (CV)',
                'finance_package' => 'Standard Bookkeeping',
                'tax_package' => 'PPh Final PP 55 & PPh 21',
                'addon' => 'Laporan Arus Kas Bulanan',
                'package_detail' => 'Pencatatan kas dan bank harian, penyusunan neraca dan laba rugi bulanan, setor PPh PP 55 0.5%.',
                'status' => 'active',
                'files' => 'https://drive.google.com/drive/folders/konsulin-akuntansi-maju',
                'review_approval' => 'Approved',
                'tax_pic' => 'Nadia Tax Consultant',
                'accounting_pic' => 'Bagus Accountant',
            ],
            [
                'client_code' => 'CLI-2026-003',
                'name' => 'PT Retail Nusantara Sejahtera',
                'email' => 'tax@retail.test',
                'phone' => '0833333333',
                'tax_id' => '77.888.999.0-111.000',
                'migration_date' => '2026-03-01',
                'client_pic' => 'Hendrik Tan (General Manager)',
                'location' => 'Surabaya, Jawa Timur',
                'tax_status' => 'PKP',
                'business_type' => 'Distribusi Logistik & FMCG',
                'contract_status' => 'In Review',
                'start_date' => '2026-03-01',
                'contract_duration_months' => 12,
                'end_contract_due_date' => '2027-02-28',
                'client_type' => 'Badan (PT)',
                'finance_package' => 'Enterprise Financial Consolidation',
                'tax_package' => 'Corporate Tax Planning & Compliance',
                'addon' => 'Restitusi PPN & Transfer Pricing Doc',
                'package_detail' => 'Konsolidasi pembukuan 3 cabang, e-Faktur PPN masa, dan penelaahan kepatuhan pajak tahun berjalan.',
                'status' => 'active',
                'files' => 'https://drive.google.com/drive/folders/konsulin-retail-nusantara',
                'review_approval' => 'Pending Review',
                'tax_pic' => 'Dewi Partner',
                'accounting_pic' => 'Ari Senior Accountant',
            ],
        ])->map(function (array $clientData) {
            $client = Client::create($clientData);

            // Seed 10 monthly compliances (Mar 26 - Dec 26)
            foreach (Client::COMPLIANCE_PERIODS as $idx => $period) {
                $isEarlyMonth = in_array($period, ['Mar 26', 'Apr 26', 'May 26']);
                ClientCompliance::create([
                    'client_id' => $client->id,
                    'period' => $period,
                    'pph_21' => $isEarlyMonth ? 'Done' : ($idx === 3 ? 'Pending Bukti Potong' : 'In Progress'),
                    'pph_unifikasi' => $isEarlyMonth ? 'Done' : ($idx === 3 ? 'Drafting' : '-'),
                    'ppn' => $client->tax_status === 'PKP' ? ($isEarlyMonth ? 'Done' : 'Pending Faktur') : 'N/A',
                    'pp_55' => $client->tax_status === 'Non-PKP' ? 'Done' : 'N/A',
                    'pph_25' => $isEarlyMonth ? 'Done' : 'Pending NTPN',
                    'lk' => $isEarlyMonth ? 'Final' : ($idx === 3 ? 'Draft' : '-'),
                    'notes' => $isEarlyMonth ? 'Selesai tepat waktu sebelum batas lapor.' : 'Menunggu rekonsiliasi rekening koran.',
                ]);
            }

            return $client;
        });

        $categories = collect([
            ['name' => 'Tax Compliance', 'description' => 'Monthly and annual tax compliance work.', 'is_active' => true],
            ['name' => 'Financial Statement', 'description' => 'Financial report and accounting preparation.', 'is_active' => true],
            ['name' => 'Finance & Tax', 'description' => 'Combined accounting and tax project.', 'is_active' => true],
        ])->map(fn (array $category) => ProjectCategory::firstOrCreate(['name' => $category['name']], $category));

        $staff = collect([
            ['name' => 'Ari Accounting', 'email' => 'ari@konsulin.test', 'phone' => '0811111111', 'type' => 'accounting', 'position' => 'Senior Accountant', 'is_active' => true],
            ['name' => 'Nadia Tax', 'email' => 'nadia.tax@konsulin.test', 'phone' => '0822222222', 'type' => 'tax', 'position' => 'Tax Consultant', 'is_active' => true],
            ['name' => 'Laras Legal', 'email' => 'laras@konsulin.test', 'phone' => '0833333333', 'type' => 'legal', 'position' => 'Legal Officer', 'is_active' => true],
            ['name' => 'Dimas Marketing', 'email' => 'dimas@konsulin.test', 'phone' => '0844444444', 'type' => 'marketing', 'position' => 'Account Executive', 'is_active' => true],
            ['name' => 'Bima IT', 'email' => 'bima@konsulin.test', 'phone' => '0855555555', 'type' => 'it', 'position' => 'IT Support', 'is_active' => true],
        ])->map(fn (array $employee) => Staff::firstOrCreate(['email' => $employee['email']], $employee));

        $clients->each(function (Client $client, int $index) use ($boss, $reviewer, $employees, $categories, $staff): void {
            $projectName = [
                'Monthly Tax Compliance',
                'Annual Financial Statement',
                'VAT Reconciliation'
            ][$index];

            $project = Project::firstOrCreate(
                [
                    'client_id' => $client->id,
                    'name' => $projectName,
                ],
                [
                    'project_category_id' => $categories[$index]->id,
                    'created_by' => $boss->id,
                    'reviewer_id' => $boss->id,
                    'service_type' => ['Tax', 'Accounting', 'Tax'][$index],
                    'status' => ['in_progress', 'waiting_client', 'completed'][$index],
                    'priority' => ['high', 'medium', 'urgent'][$index],
                    'start_date' => now()->subDays(7 - $index)->toDateString(),
                    'due_date' => now()->addDays(10 + ($index * 7))->toDateString(),
                    'description' => 'Client service project with tracked tasks, progress, and risks.',
                ]
            );

            $project->staff()->sync([
                $staff[0]->id => ['role' => 'pic_accounting'],
                $staff[1]->id => ['role' => 'pic_tax'],
            ]);

            $taskTitle = [
                'Collect VAT invoices',
                'Prepare trial balance',
                'Review purchase tax evidence'
            ][$index];

            $task = ProjectTask::firstOrCreate(
                [
                    'project_id' => $project->id,
                    'title' => $taskTitle,
                ],
                [
                    'assigned_to' => $employees[$index]->id,
                    'status' => ['in_review', 'waiting_client', 'completed'][$index],
                    'review_status' => ['pending', 'none', 'approved'][$index],
                    'reviewed_by' => $index === 2 ? $reviewer->id : null,
                    'reviewed_at' => $index === 2 ? now()->subDay() : null,
                    'progress_percent' => [85, 30, 100][$index],
                    'due_date' => now()->addDays(4 + $index)->toDateString(),
                ]
            );

            $task->populateDefaultChecklists();

            if ($index === 2) {
                // Check all items for completed task
                $task->checklists()->update([
                    'is_checked' => true,
                    'checked_by' => $reviewer->id,
                    'checked_at' => now()->subDay(),
                ]);

                \App\Models\TaskReview::firstOrCreate(
                    [
                        'project_task_id' => $task->id,
                        'reviewer_id' => $reviewer->id,
                    ],
                    [
                        'action' => 'approved',
                        'notes' => 'Rekonsiliasi PPN valid, seluruh bukti potong terlampir dan NTPN terkonfirmasi.',
                    ]
                );
            } elseif ($index === 0) {
                // Check 2 of 4 items for in_review task
                $firstChecklist = $task->checklists()->first();
                if ($firstChecklist) {
                    $firstChecklist->update([
                        'is_checked' => true,
                        'checked_by' => $employees[0]->id,
                        'checked_at' => now()->subHours(2),
                    ]);
                }
            }

            ProjectProgressUpdate::firstOrCreate(
                [
                    'project_id' => $project->id,
                    'project_task_id' => $task->id,
                    'user_id' => $employees[$index]->id,
                ],
                [
                    'progress_percent' => $task->progress_percent,
                    'summary' => 'Initial progress has been recorded for boss monitoring.',
                ]
            );

            // P2: Populate and seed realistic Client Input Documents
            $project->populateDefaultDocuments();

            if ($index === 2) {
                // Completed project: all documents verified
                $project->documents()->update([
                    'status' => 'verified',
                    'received_at' => now()->subDays(3),
                    'verified_at' => now()->subDays(2),
                    'verified_by' => $reviewer->id,
                    'file_url' => 'https://drive.google.com/drive/folders/konsulin-client-docs-archive',
                ]);
            } elseif ($index === 0) {
                // Tax project: 1 verified, 1 received, 1 pending critical overdue
                $docs = $project->documents()->get();
                if ($docs->count() >= 3) {
                    $docs[0]->update([
                        'status' => 'verified',
                        'received_at' => now()->subDays(2),
                        'verified_at' => now()->subDay(),
                        'verified_by' => $reviewer->id,
                        'file_url' => 'https://drive.google.com/file/d/faktur-pajak-keluaran',
                    ]);
                    $docs[1]->update([
                        'status' => 'received',
                        'received_at' => now()->subDay(),
                        'file_url' => 'https://drive.google.com/file/d/rekap-pembelian',
                    ]);
                    $docs[2]->update([
                        'status' => 'pending',
                        'due_date' => now()->subDays(2),
                    ]);
                    $docs[2]->escalateToThreat($employees[0], 'Menunggu kiriman e-statement dari direktur keuangan.');
                }
            } else {
                // Accounting project: partial document delivered
                $docs = $project->documents()->get();
                if ($docs->count() >= 2) {
                    $docs[0]->update([
                        'status' => 'partial',
                        'received_at' => now()->subDay(),
                        'notes' => 'Baru rekening koran Mandiri yang dikirim, rekening BCA belum ada.',
                    ]);
                }
            }

            if ($index === 1) {
                ProjectThreat::firstOrCreate(
                    [
                        'project_id' => $project->id,
                        'title' => 'Bank statement for final week missing',
                    ],
                    [
                        'project_task_id' => $task->id,
                        'user_id' => $employees[$index]->id,
                        'severity' => 'high',
                        'status' => 'open',
                        'description' => 'Follow up needed with client PIC.',
                        'mitigation_plan' => 'Contact PIC and escalate before due date.',
                    ]
                );
            }
        });
    }
}
