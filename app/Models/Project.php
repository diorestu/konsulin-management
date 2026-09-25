<?php

namespace App\Models;

use Database\Factories\ProjectFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    /** @use HasFactory<ProjectFactory> */
    use HasFactory;

    protected $fillable = [
        'client_id',
        'project_category_id',
        'created_by',
        'reviewer_id',
        'name',
        'service_type',
        'status',
        'priority',
        'estimated_hours',
        'start_date',
        'due_date',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'due_date' => 'date',
            'estimated_hours' => 'decimal:2',
        ];
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function category()
    {
        return $this->belongsTo(ProjectCategory::class, 'project_category_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewer_id')->withDefault(function () {
            return $this->creator;
        });
    }

    public function tasks()
    {
        return $this->hasMany(ProjectTask::class);
    }

    public function staff()
    {
        return $this->belongsToMany(Staff::class)->withPivot('role')->withTimestamps();
    }

    public function accountingStaff()
    {
        return $this->belongsToMany(Staff::class)
            ->where(function ($query) {
                $query->where('project_staff.role', 'pic_accounting')
                    ->orWhere(function ($q) {
                        $q->whereNull('project_staff.role')->where('staff.type', 'accounting');
                    });
            })
            ->withPivot('role')
            ->withTimestamps();
    }

    public function taxStaff()
    {
        return $this->belongsToMany(Staff::class)
            ->where(function ($query) {
                $query->where('project_staff.role', 'pic_tax')
                    ->orWhere(function ($q) {
                        $q->whereNull('project_staff.role')->where('staff.type', 'tax');
                    });
            })
            ->withPivot('role')
            ->withTimestamps();
    }

    public function progressUpdates()
    {
        return $this->hasMany(ProjectProgressUpdate::class)->latest();
    }

    public function threats()
    {
        return $this->hasMany(ProjectThreat::class)->latest();
    }

    public function documents()
    {
        return $this->hasMany(ClientDocument::class)->orderBy('is_critical', 'desc')->orderBy('due_date', 'asc');
    }

    public function populateDefaultDocuments(?string $type = null): int
    {
        $resolvedType = $type;
        if (! $resolvedType) {
            $service = strtolower($this->service_type ?? '');
            if (str_contains($service, 'tax') || str_contains($service, 'pajak')) {
                $resolvedType = 'tax';
            } elseif (str_contains($service, 'account') || str_contains($service, 'pembukuan') || str_contains($service, 'bookkeeping')) {
                $resolvedType = 'accounting';
            } else {
                $resolvedType = 'advisory';
            }
        }

        $templates = match ($resolvedType) {
            'tax' => [
                ['title' => 'Rekap Penjualan & Faktur Pajak Keluaran', 'category' => 'Penjualan', 'is_critical' => true, 'days' => 5],
                ['title' => 'Rekap Pembelian & Faktur Pajak Masukan', 'category' => 'Pembelian', 'is_critical' => true, 'days' => 5],
                ['title' => 'Rekening Koran Operasional Seluruh Bank', 'category' => 'Bank', 'is_critical' => true, 'days' => 7],
                ['title' => 'Bukti Potong PPh 21 / 23 / Final', 'category' => 'Bukti Potong', 'is_critical' => false, 'days' => 10],
                ['title' => 'Daftar Gaji / Payroll & Bukti Bayar BPJS', 'category' => 'Payroll', 'is_critical' => false, 'days' => 10],
                ['title' => 'Laporan Keuangan Sementara / Trial Balance', 'category' => 'Laporan Keuangan', 'is_critical' => true, 'days' => 7],
            ],
            'accounting' => [
                ['title' => 'Rekening Koran Seluruh Rekening Bank', 'category' => 'Bank', 'is_critical' => true, 'days' => 5],
                ['title' => 'Bukti Transaksi Kas Keluar & Petty Cash', 'category' => 'Kas & Bank', 'is_critical' => true, 'days' => 5],
                ['title' => 'Faktur Penjualan & Surat Jalan', 'category' => 'Penjualan', 'is_critical' => true, 'days' => 7],
                ['title' => 'Faktur & Kuitansi Pembelian Supplier', 'category' => 'Pembelian', 'is_critical' => true, 'days' => 7],
                ['title' => 'Daftar Aset Tetap & Penyusutan Berjalan', 'category' => 'Aset', 'is_critical' => false, 'days' => 12],
                ['title' => 'Rekap Saldo Piutang & Hutang Usaha', 'category' => 'Buku Besar', 'is_critical' => false, 'days' => 10],
            ],
            default => [
                ['title' => 'Akta Pendirian & Perubahan Terakhir', 'category' => 'Legalitas', 'is_critical' => true, 'days' => 7],
                ['title' => 'Surat Keputusan Kemenkumham & NIB', 'category' => 'Legalitas', 'is_critical' => false, 'days' => 7],
                ['title' => 'Laporan Keuangan Audited Tahun Lalu', 'category' => 'Laporan Keuangan', 'is_critical' => true, 'days' => 7],
                ['title' => 'Dokumen Kebijakan & Kontrak Utama', 'category' => 'Kontrak', 'is_critical' => false, 'days' => 14],
                ['title' => 'Konfirmasi Saldo Bank & Pihak Terkait', 'category' => 'Konfirmasi', 'is_critical' => true, 'days' => 10],
            ],
        };

        $createdCount = 0;
        $baseDate = $this->start_date ? \Carbon\Carbon::parse($this->start_date) : \Carbon\Carbon::today();

        foreach ($templates as $item) {
            $exists = $this->documents()->where('title', $item['title'])->exists();
            if ($exists) {
                continue;
            }

            $dueDate = $baseDate->copy()->addDays($item['days']);
            if ($this->due_date && $dueDate->greaterThan(\Carbon\Carbon::parse($this->due_date))) {
                $dueDate = \Carbon\Carbon::parse($this->due_date);
            }

            $this->documents()->create([
                'client_id' => $this->client_id,
                'title' => $item['title'],
                'category' => $item['category'],
                'status' => 'pending',
                'is_critical' => $item['is_critical'],
                'due_date' => $dueDate,
            ]);

            $createdCount++;
        }

        return $createdCount;
    }

    public function progressPercent(): int
    {
        if ($this->tasks->isEmpty()) {
            return 0;
        }

        return (int) round($this->tasks->avg('progress_percent'));
    }

    public function scopeVisibleTo($query, ?User $user = null)
    {
        if (! $user) {
            return $query;
        }

        if ($user->isAdmin() || $user->isReviewer()) {
            return $query;
        }

        // Staff only sees projects assigned to them
        return $query->where(function ($q) use ($user) {
            $q->whereHas('tasks', function ($taskQuery) use ($user) {
                $taskQuery->where('assigned_to', $user->id);
            })->orWhereHas('staff', function ($staffQuery) use ($user) {
                $staffQuery->where('staff.email', $user->email);
            });
        });
    }

    public function isAssignedTo(?User $user = null): bool
    {
        if (! $user) {
            return false;
        }

        if ($user->isAdmin() || $user->isReviewer()) {
            return true;
        }

        return $this->tasks()->where('assigned_to', $user->id)->exists()
            || $this->staff()->where('staff.email', $user->email)->exists();
    }

    public function totalLoggedSeconds(): int
    {
        if ($this->relationLoaded('tasks')) {
            $total = 0;
            foreach ($this->tasks as $task) {
                $total += $task->totalDurationSeconds();
            }
            return $total;
        }

        return (int) TaskTimeLog::whereHas('task', function ($q) {
            $q->where('project_id', $this->id);
        })->where('status', 'completed')->sum('duration_seconds');
    }

    public function formattedTotalLoggedTime(): string
    {
        $totalSec = $this->totalLoggedSeconds();
        if ($totalSec === 0) {
            return '0 jam';
        }
        $hours = floor($totalSec / 3600);
        $mins = floor(($totalSec % 3600) / 60);
        if ($hours > 0 && $mins > 0) {
            return "{$hours}j {$mins}m";
        } elseif ($hours > 0) {
            return "{$hours} jam";
        }
        return "{$mins} menit";
    }

    public function projectBudgetHours(): float
    {
        return (float) ($this->estimated_hours ?? 0);
    }

    public function tasksTotalEstimatedHours(): float
    {
        if ($this->relationLoaded('tasks')) {
            return round((float) $this->tasks->sum('estimated_hours'), 2);
        }

        return round((float) $this->tasks()->sum('estimated_hours'), 2);
    }

    public function effectiveEstimatedHours(): float
    {
        $budget = $this->projectBudgetHours();
        if ($budget > 0) {
            return $budget;
        }

        return $this->tasksTotalEstimatedHours();
    }

    public function totalLoggedHours(): float
    {
        return round($this->totalLoggedSeconds() / 3600, 2);
    }

    public function burnRatePercent(): int
    {
        $estimate = $this->effectiveEstimatedHours();
        if ($estimate <= 0) {
            return 0;
        }

        return (int) round(($this->totalLoggedHours() / $estimate) * 100);
    }

    public function budgetStatus(): string
    {
        $estimate = $this->effectiveEstimatedHours();
        if ($estimate <= 0) {
            return 'no_estimate';
        }

        $actual = $this->totalLoggedHours();
        if ($actual > $estimate) {
            return 'over_budget';
        }

        if ($this->burnRatePercent() >= 80) {
            return 'warning';
        }

        return 'on_track';
    }

    public function remainingHours(): float
    {
        $estimate = $this->effectiveEstimatedHours();
        return max(0, round($estimate - $this->totalLoggedHours(), 2));
    }

    public function overBudgetHours(): float
    {
        $estimate = $this->effectiveEstimatedHours();
        return max(0, round($this->totalLoggedHours() - $estimate, 2));
    }
}
