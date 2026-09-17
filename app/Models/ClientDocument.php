<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientDocument extends Model
{
    protected $fillable = [
        'project_id',
        'client_id',
        'title',
        'category',
        'status',
        'is_critical',
        'due_date',
        'received_at',
        'verified_at',
        'verified_by',
        'threat_id',
        'file_url',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'is_critical' => 'boolean',
            'due_date' => 'date',
            'received_at' => 'datetime',
            'verified_at' => 'datetime',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function threat(): BelongsTo
    {
        return $this->belongsTo(ProjectThreat::class, 'threat_id');
    }

    public function isOverdue(): bool
    {
        return $this->due_date
            && $this->due_date->isPast()
            && in_array($this->status, ['pending', 'partial']);
    }

    public function markAsReceived(?string $fileUrl = null, ?string $notes = null): self
    {
        $this->update([
            'status' => 'received',
            'received_at' => Carbon::now(),
            'file_url' => $fileUrl ?? $this->file_url,
            'notes' => $notes ?? $this->notes,
        ]);

        $this->resolveAssociatedThreat();

        return $this;
    }

    public function markAsVerified(User $verifier): self
    {
        $this->update([
            'status' => 'verified',
            'received_at' => $this->received_at ?? Carbon::now(),
            'verified_at' => Carbon::now(),
            'verified_by' => $verifier->id,
        ]);

        $this->resolveAssociatedThreat();

        return $this;
    }

    public function escalateToThreat(User $user, ?string $customNotes = null): ProjectThreat
    {
        // If already linked to an open threat, return it
        if ($this->threat && $this->threat->status === 'open') {
            return $this->threat;
        }

        $isPastDue = $this->due_date && $this->due_date->isPast();
        $severity = $isPastDue ? 'critical' : ($this->is_critical ? 'high' : 'medium');
        $clientName = $this->client?->name ?? 'Klien';
        $clientPic = $this->client?->client_pic ?? 'PIC Klien';

        $threat = ProjectThreat::create([
            'project_id' => $this->project_id,
            'user_id' => $user->id,
            'title' => 'Dokumen Tertahan: ' . $this->title,
            'severity' => $severity,
            'status' => 'open',
            'description' => 'Klien ' . $clientName . ' belum menyerahkan dokumen wajib "' . $this->title . '" (Tenggat: ' . ($this->due_date ? $this->due_date->format('d M Y') : 'Segera') . '). ' . ($customNotes ? 'Catatan: ' . $customNotes : 'Pekerjaan berpotensi tertunda.'),
            'mitigation_plan' => 'Segera hubungi ' . $clientPic . ' untuk follow up penyerahan berkas.',
        ]);

        $this->update(['threat_id' => $threat->id]);

        return $threat;
    }

    public function resolveAssociatedThreat(): void
    {
        if ($this->threat && $this->threat->status !== 'resolved') {
            $this->threat->update([
                'status' => 'resolved',
                'mitigation_plan' => ($this->threat->mitigation_plan ? $this->threat->mitigation_plan . ' · ' : '') . 'Dokumen telah diterima dan diverifikasi pada ' . Carbon::now()->format('d M Y H:i') . '.',
            ]);
        }
    }
}
