<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectTask extends Model
{
    protected $fillable = [
        'project_id',
        'assigned_to',
        'reviewed_by',
        'title',
        'status',
        'review_status',
        'progress_percent',
        'estimated_hours',
        'due_date',
        'reviewed_at',
        'notes',
        'review_notes',
    ];

    protected function casts(): array
    {
        return [
            'due_date' => 'date',
            'reviewed_at' => 'datetime',
            'progress_percent' => 'integer',
            'estimated_hours' => 'decimal:2',
        ];
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function progressUpdates()
    {
        return $this->hasMany(ProjectProgressUpdate::class);
    }

    public function threats()
    {
        return $this->hasMany(ProjectThreat::class);
    }

    public function timeLogs()
    {
        return $this->hasMany(TaskTimeLog::class);
    }

    public function totalDurationSeconds(): int
    {
        if ($this->relationLoaded('timeLogs')) {
            return (int) $this->timeLogs->where('status', 'completed')->sum('duration_seconds');
        }

        return (int) $this->timeLogs()->where('status', 'completed')->sum('duration_seconds');
    }

    public function actualLoggedHours(): float
    {
        return round($this->totalDurationSeconds() / 3600, 2);
    }

    public function actualLoggedMinutes(): int
    {
        return (int) round($this->totalDurationSeconds() / 60);
    }

    public function formattedActualLoggedTime(): string
    {
        $sec = $this->totalDurationSeconds();
        if ($sec === 0) {
            return '0m';
        }
        $hours = floor($sec / 3600);
        $mins = floor(($sec % 3600) / 60);
        if ($hours > 0 && $mins > 0) {
            return "{$hours}j {$mins}m";
        } elseif ($hours > 0) {
            return "{$hours}j";
        }
        return "{$mins}m";
    }

    public function burnRatePercent(): int
    {
        $estimate = (float) ($this->estimated_hours ?? 0);
        if ($estimate <= 0) {
            return 0;
        }

        return (int) round(($this->actualLoggedHours() / $estimate) * 100);
    }

    public function budgetStatus(): string
    {
        $estimate = (float) ($this->estimated_hours ?? 0);
        if ($estimate <= 0) {
            return 'no_estimate';
        }

        $actual = $this->actualLoggedHours();
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
        $estimate = (float) ($this->estimated_hours ?? 0);
        return max(0, round($estimate - $this->actualLoggedHours(), 2));
    }

    public function overBudgetHours(): float
    {
        $estimate = (float) ($this->estimated_hours ?? 0);
        return max(0, round($this->actualLoggedHours() - $estimate, 2));
    }

    public function activeTimeLogFor(?int $userId = null)
    {
        $query = $this->timeLogs()->where('status', 'running')->whereNull('stopped_at');
        if ($userId) {
            $query->where('user_id', $userId);
        }
        return $query->latest('started_at')->first();
    }

    public function checklists()
    {
        return $this->hasMany(TaskChecklist::class)->orderBy('order', 'asc')->orderBy('id', 'asc');
    }

    public function reviews()
    {
        return $this->hasMany(TaskReview::class)->latest();
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function isReviewPending(): bool
    {
        return $this->status === 'in_review' || $this->review_status === 'pending';
    }

    public function isRevisionRequested(): bool
    {
        return $this->review_status === 'revision_requested';
    }

    public function isApproved(): bool
    {
        return $this->review_status === 'approved' || ($this->status === 'completed' && !is_null($this->reviewed_by));
    }

    public function canBeReviewedBy(?User $user = null): bool
    {
        if (! $user) {
            return false;
        }

        return $user->isAdmin() || $user->isReviewer();
    }

    public function canBeUpdatedBy(?User $user = null): bool
    {
        if (! $user) {
            return false;
        }

        if ($user->isAdmin() || $user->isReviewer()) {
            return true;
        }

        // Staff cannot update tasks assigned to other users
        if (! is_null($this->assigned_to) && (int) $this->assigned_to !== (int) $user->id) {
            return false;
        }

        return true;
    }

    public function populateDefaultChecklists(): void
    {
        if ($this->checklists()->exists()) {
            return;
        }

        $serviceType = strtolower($this->project?->service_type ?? '');
        $items = [];

        if (str_contains($serviceType, 'tax') || str_contains($serviceType, 'pajak') || str_contains($serviceType, 'spt')) {
            $items = [
                'Rekonsiliasi faktur pajak masukan & keluaran dengan buku besar',
                'Verifikasi keabsahan NTPN & bukti setor penerimaan negara',
                'Validasi perhitungan kompensasi lebih bayar / kredit pajak',
                'Pengecekan kelengkapan lampiran dokumen pendukung SPT',
            ];
        } elseif (str_contains($serviceType, 'accounting') || str_contains($serviceType, 'akuntansi') || str_contains($serviceType, 'financial')) {
            $items = [
                'Rekonsiliasi rekening koran bank & kas fisik akhir periode',
                'Verifikasi jurnal penyesuaian depresiasi, amortisasi & akrual',
                'Validasi keselarasan neraca saldo (trial balance) & laba rugi',
                'Pengecekan kelengkapan catatan atas laporan keuangan (CALK)',
            ];
        } else {
            $items = [
                'Verifikasi kelengkapan dokumen dasar masukan dari klien',
                'Pemeriksaan kepatuhan regulasi perpajakan / akuntansi terkait',
                'Validasi kertas kerja internal dan kalkulasi perhitungan',
                'Pengecekan kesiapan deliverable sebelum diserahkan ke klien',
            ];
        }

        foreach ($items as $index => $item) {
            $this->checklists()->create([
                'title' => $item,
                'is_checked' => false,
                'order' => $index + 1,
            ]);
        }
    }
}
