<?php

namespace App\Http\Controllers;

use App\Models\ClientDocument;
use App\Models\Project;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ClientDocumentController extends Controller
{
    /**
     * Store a newly created document requirement in storage.
     */
    public function store(Request $request, Project $project): JsonResponse|RedirectResponse
    {
        $this->authorizeProjectAccess($request->user(), $project);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'max:100'],
            'due_date' => ['nullable', 'date'],
            'is_critical' => ['nullable', 'boolean'],
            'file_url' => ['nullable', 'string', 'max:500'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $document = $project->documents()->create([
            'client_id' => $project->client_id,
            'title' => $validated['title'],
            'category' => $validated['category'],
            'due_date' => $validated['due_date'] ?? null,
            'is_critical' => $request->boolean('is_critical'),
            'file_url' => $validated['file_url'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'status' => 'pending',
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Dokumen masukan "' . $document->title . '" berhasil ditambahkan ke checklist.',
                'document' => $this->formatDocumentData($document->fresh(['verifier', 'threat'])),
                'summary' => $this->getDocumentSummary($project),
            ]);
        }

        return redirect()->route('projects.show', $project)->with('status', 'Dokumen masukan berhasil ditambahkan.');
    }

    /**
     * Update the status and metadata of the specified document.
     */
    public function updateStatus(Request $request, Project $project, ClientDocument $document): JsonResponse|RedirectResponse
    {
        $this->authorizeProjectAccess($request->user(), $project);

        if ($document->project_id !== $project->id) {
            abort(404, 'Dokumen tidak ditemukan pada proyek ini.');
        }

        $validated = $request->validate([
            'status' => ['required', 'string', 'in:pending,partial,received,verified'],
            'file_url' => ['nullable', 'string', 'max:500'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $status = $validated['status'];
        $user = $request->user();

        $updateData = [
            'status' => $status,
        ];

        if ($request->has('file_url')) {
            $updateData['file_url'] = $validated['file_url'];
        }

        if ($request->has('notes')) {
            $updateData['notes'] = $validated['notes'];
        }

        if ($status === 'verified') {
            $updateData['received_at'] = $document->received_at ?? Carbon::now();
            $updateData['verified_at'] = Carbon::now();
            $updateData['verified_by'] = $user->id;
            $document->update($updateData);
            $document->resolveAssociatedThreat();
        } elseif ($status === 'received') {
            $updateData['received_at'] = $document->received_at ?? Carbon::now();
            $updateData['verified_at'] = null;
            $updateData['verified_by'] = null;
            $document->update($updateData);
            $document->resolveAssociatedThreat();
        } else {
            // pending or partial
            $updateData['verified_at'] = null;
            $updateData['verified_by'] = null;
            $document->update($updateData);
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Status dokumen "' . $document->title . '" diperbarui menjadi ' . $this->statusLabel($status) . '.',
                'document' => $this->formatDocumentData($document->fresh(['verifier', 'threat'])),
                'summary' => $this->getDocumentSummary($project),
            ]);
        }

        return redirect()->route('projects.show', $project)->with('status', 'Status dokumen berhasil diperbarui.');
    }

    /**
     * Escalate an overdue/pending document into an active ProjectThreat.
     */
    public function escalateThreat(Request $request, Project $project, ClientDocument $document): JsonResponse|RedirectResponse
    {
        $this->authorizeProjectAccess($request->user(), $project);

        if ($document->project_id !== $project->id) {
            abort(404, 'Dokumen tidak ditemukan pada proyek ini.');
        }

        $notes = $request->input('notes');
        $threat = $document->escalateToThreat($request->user(), $notes);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Kendala dokumen "' . $document->title . '" berhasil dieskalasi ke Project Threat!',
                'threat_id' => $threat->id,
                'threat_title' => $threat->title,
                'threat_severity' => $threat->severity,
                'document' => $this->formatDocumentData($document->fresh(['verifier', 'threat'])),
                'open_threats' => $project->threats()->where('status', 'open')->count(),
            ]);
        }

        return redirect()->route('projects.show', $project)->with('status', 'Kendala dokumen berhasil dieskalasi.');
    }

    /**
     * Populate standard checklist items for this project based on engagement type.
     */
    public function populateDefaults(Request $request, Project $project): JsonResponse|RedirectResponse
    {
        $this->authorizeProjectAccess($request->user(), $project);

        $type = $request->input('type');
        $createdCount = $project->populateDefaultDocuments($type);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $createdCount > 0
                    ? $createdCount . ' dokumen standar berhasil dimuat ke dalam checklist.'
                    : 'Seluruh dokumen standar untuk jenis penugasan ini sudah tersedia.',
                'created_count' => $createdCount,
                'documents' => $project->fresh()->documents->load(['verifier', 'threat'])->map(fn($d) => $this->formatDocumentData($d)),
                'summary' => $this->getDocumentSummary($project),
            ]);
        }

        return redirect()->route('projects.show', $project)->with('status', 'Checklist dokumen standar berhasil dimuat.');
    }

    /**
     * Remove a document from the project checklist.
     */
    public function destroy(Request $request, Project $project, ClientDocument $document): JsonResponse|RedirectResponse
    {
        $this->authorizeProjectAccess($request->user(), $project);

        if ($document->project_id !== $project->id) {
            abort(404, 'Dokumen tidak ditemukan pada proyek ini.');
        }

        $document->delete();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Dokumen berhasil dihapus dari checklist.',
                'summary' => $this->getDocumentSummary($project),
            ]);
        }

        return redirect()->route('projects.show', $project)->with('status', 'Dokumen berhasil dihapus.');
    }

    /**
     * Ensure current user has access to view/modify the project.
     */
    protected function authorizeProjectAccess(User $user, Project $project): void
    {
        if (! $project->isAssignedTo($user)) {
            abort(403, 'Akses ditolak. Anda tidak ditugaskan pada proyek ini.');
        }
    }

    /**
     * Helper to format document payload for JSON response.
     */
    protected function formatDocumentData(ClientDocument $doc): array
    {
        return [
            'id' => $doc->id,
            'title' => $doc->title,
            'category' => $doc->category,
            'status' => $doc->status,
            'status_label' => $this->statusLabel($doc->status),
            'is_critical' => (bool) $doc->is_critical,
            'is_overdue' => $doc->isOverdue(),
            'due_date' => $doc->due_date ? $doc->due_date->format('d M Y') : null,
            'due_date_raw' => $doc->due_date ? $doc->due_date->format('Y-m-d') : null,
            'received_at' => $doc->received_at ? $doc->received_at->format('d M Y, H:i') : null,
            'verified_at' => $doc->verified_at ? $doc->verified_at->format('d M Y, H:i') : null,
            'verifier_name' => $doc->verifier?->name,
            'file_url' => $doc->file_url,
            'notes' => $doc->notes,
            'threat_id' => $doc->threat_id,
            'has_active_threat' => $doc->threat && $doc->threat->status === 'open',
        ];
    }

    /**
     * Get aggregate statistics for project documents.
     */
    protected function getDocumentSummary(Project $project): array
    {
        $docs = $project->documents()->get();
        $total = $docs->count();
        $received = $docs->whereIn('status', ['received', 'verified'])->count();
        $verified = $docs->where('status', 'verified')->count();
        $pending = $docs->where('status', 'pending')->count();
        $partial = $docs->where('status', 'partial')->count();
        $criticalPending = $docs->where('is_critical', true)->whereIn('status', ['pending', 'partial'])->count();

        $percent = $total > 0 ? (int) round(($received / $total) * 100) : 0;

        return [
            'total' => $total,
            'received' => $received,
            'verified' => $verified,
            'pending' => $pending,
            'partial' => $partial,
            'critical_pending' => $criticalPending,
            'percentage' => $percent,
        ];
    }

    protected function statusLabel(string $status): string
    {
        return match ($status) {
            'verified' => 'Diverifikasi',
            'received' => 'Diterima',
            'partial' => 'Sebagian / Kurang',
            default => 'Belum Diterima',
        };
    }
}
