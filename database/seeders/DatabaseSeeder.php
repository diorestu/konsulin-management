<?php

namespace Database\Seeders;

use App\Models\Client;
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
            ['key' => 'hero', 'label' => 'Homepage Hero', 'type' => 'hero', 'title' => 'Konsultasi yang membuat bisnis lebih siap.', 'body' => 'Konsulin membantu bisnis menata pajak, akuntansi, dan keputusan finansial dengan pendampingan yang jelas.', 'button_text' => 'Mulai Konsultasi', 'button_url' => '#contact', 'sort_order' => 10],
            ['key' => 'services', 'label' => 'Services Intro', 'type' => 'section', 'title' => 'Keahlian yang bekerja untuk Anda.', 'body' => 'Dari kepatuhan pajak sampai laporan keuangan, pilih dukungan yang sesuai dengan tahap bisnis Anda.', 'sort_order' => 20],
            ['key' => 'contact', 'label' => 'Contact CTA', 'type' => 'cta', 'title' => 'Siap membicarakan kebutuhan bisnis Anda?', 'body' => 'Ceritakan tantangan Anda dan tim Konsulin akan membantu menentukan langkah berikutnya.', 'button_text' => 'Hubungi Konsulin', 'button_url' => 'mailto:hello@konsulin.id', 'sort_order' => 30],
        ] as $content) {
            WebsiteContent::updateOrCreate(['key' => $content['key']], $content);
        }

        $boss = User::factory()->create([
            'name' => 'Dewi Partner',
            'email' => 'boss@konsulin.test',
            'role' => 'boss',
        ]);

        $employees = collect([
            ['name' => 'Nadia Consultant', 'email' => 'nadia@konsulin.test'],
            ['name' => 'Rafi Staff', 'email' => 'rafi@konsulin.test'],
            ['name' => 'Bagus Accountant', 'email' => 'bagus@konsulin.test'],
        ])->map(fn (array $user) => User::factory()->create($user + ['role' => 'employee']));

        $clients = collect([
            ['name' => 'PT Sinar Pajak', 'email' => 'finance@sinar.test', 'phone' => '08123456789', 'tax_id' => '12.345.678.9-012.000'],
            ['name' => 'CV Akuntansi Maju', 'email' => 'owner@maju.test', 'phone' => '0822222222', 'tax_id' => '98.765.432.1-000.000'],
            ['name' => 'PT Retail Nusantara', 'email' => 'tax@retail.test', 'phone' => '0833333333', 'tax_id' => '77.888.999.0-111.000'],
        ])->map(fn (array $client) => Client::create($client));

        $categories = collect([
            ['name' => 'Tax Compliance', 'description' => 'Monthly and annual tax compliance work.', 'is_active' => true],
            ['name' => 'Financial Statement', 'description' => 'Financial report and accounting preparation.', 'is_active' => true],
            ['name' => 'Finance & Tax', 'description' => 'Combined accounting and tax project.', 'is_active' => true],
        ])->map(fn (array $category) => ProjectCategory::create($category));

        $staff = collect([
            ['name' => 'Ari Accounting', 'email' => 'ari@konsulin.test', 'phone' => '0811111111', 'type' => 'accounting', 'position' => 'Senior Accountant', 'is_active' => true],
            ['name' => 'Nadia Tax', 'email' => 'nadia.tax@konsulin.test', 'phone' => '0822222222', 'type' => 'tax', 'position' => 'Tax Consultant', 'is_active' => true],
            ['name' => 'Laras Legal', 'email' => 'laras@konsulin.test', 'phone' => '0833333333', 'type' => 'legal', 'position' => 'Legal Officer', 'is_active' => true],
            ['name' => 'Dimas Marketing', 'email' => 'dimas@konsulin.test', 'phone' => '0844444444', 'type' => 'marketing', 'position' => 'Account Executive', 'is_active' => true],
            ['name' => 'Bima IT', 'email' => 'bima@konsulin.test', 'phone' => '0855555555', 'type' => 'it', 'position' => 'IT Support', 'is_active' => true],
        ])->map(fn (array $employee) => Staff::create($employee));

        $clients->each(function (Client $client, int $index) use ($boss, $employees, $categories, $staff): void {
            $project = Project::create([
                'client_id' => $client->id,
                'project_category_id' => $categories[$index]->id,
                'created_by' => $boss->id,
                'name' => ['Monthly Tax Compliance', 'Annual Financial Statement', 'VAT Reconciliation'][$index],
                'service_type' => ['Tax', 'Accounting', 'Tax'][$index],
                'status' => ['in_progress', 'waiting_client', 'not_started'][$index],
                'priority' => ['high', 'medium', 'urgent'][$index],
                'start_date' => now()->subDays(7 - $index)->toDateString(),
                'due_date' => now()->addDays(10 + ($index * 7))->toDateString(),
                'description' => 'Client service project with tracked tasks, progress, and risks.',
            ]);

            $project->staff()->sync(match ($index) {
                0 => [$staff[0]->id, $staff[1]->id],
                1 => [$staff[0]->id],
                default => [$staff[1]->id, $staff[2]->id],
            });

            $task = ProjectTask::create([
                'project_id' => $project->id,
                'assigned_to' => $employees[$index]->id,
                'title' => ['Collect VAT invoices', 'Prepare trial balance', 'Review purchase tax evidence'][$index],
                'status' => ['in_progress', 'waiting_client', 'not_started'][$index],
                'progress_percent' => [40, 60, 10][$index],
                'due_date' => now()->addDays(4 + $index)->toDateString(),
            ]);

            ProjectProgressUpdate::create([
                'project_id' => $project->id,
                'project_task_id' => $task->id,
                'user_id' => $employees[$index]->id,
                'progress_percent' => $task->progress_percent,
                'summary' => 'Initial progress has been recorded for boss monitoring.',
            ]);

            if ($index === 1) {
                ProjectThreat::create([
                    'project_id' => $project->id,
                    'project_task_id' => $task->id,
                    'user_id' => $employees[$index]->id,
                    'title' => 'Client document is incomplete',
                    'severity' => 'high',
                    'status' => 'open',
                    'description' => 'Bank statement for the final week has not been sent.',
                    'mitigation_plan' => 'Follow up with client PIC and escalate before due date.',
                ]);
            }
        });
    }
}
