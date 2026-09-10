<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\ClientCompliance;
use App\Models\Staff;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClientController extends Controller
{
    public function index(Request $request): View
    {
        $query = Client::with(['compliances', 'projects']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('contract_status')) {
            $query->where('contract_status', $request->contract_status);
        }

        if ($request->filled('tax_status')) {
            $query->where('tax_status', $request->tax_status);
        }

        $clients = $query->latest()->get();

        $totalClients = $clients->count();
        $activeContracts = $clients->where('contract_status', 'Active')->count();
        $pkpCount = $clients->where('tax_status', 'PKP')->count();
        $pendingReviews = $clients->where('review_approval', 'Pending Review')->count();

        $periods = Client::COMPLIANCE_PERIODS;

        return view('clients.index', compact(
            'clients',
            'totalClients',
            'activeContracts',
            'pkpCount',
            'pendingReviews',
            'periods'
        ));
    }

    public function create(): View
    {
        $periods = Client::COMPLIANCE_PERIODS;
        $staffMembers = Staff::where('is_active', true)->orderBy('name')->get();

        return view('clients.create', compact('periods', 'staffMembers'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'client_code' => ['nullable', 'string', 'max:50'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'tax_id' => ['nullable', 'string', 'max:50'],
            'migration_date' => ['nullable', 'date'],
            'client_pic' => ['nullable', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'tax_status' => ['nullable', 'string', 'max:100'],
            'business_type' => ['nullable', 'string', 'max:150'],
            'contract_status' => ['nullable', 'string', 'max:100'],
            'start_date' => ['nullable', 'date'],
            'contract_duration_months' => ['nullable', 'integer', 'min:1'],
            'end_contract_due_date' => ['nullable', 'date'],
            'client_type' => ['nullable', 'string', 'max:100'],
            'finance_package' => ['nullable', 'string', 'max:150'],
            'tax_package' => ['nullable', 'string', 'max:150'],
            'addon' => ['nullable', 'string', 'max:255'],
            'package_detail' => ['nullable', 'string'],
            'status' => ['required', 'string', 'max:50'],
            'files' => ['nullable', 'string'],
            'review_approval' => ['nullable', 'string', 'max:100'],
            'tax_pic' => ['nullable', 'string', 'max:255'],
            'accounting_pic' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'compliances' => ['nullable', 'array'],
        ]);

        $client = Client::create($validated);

        if ($request->has('compliances') && is_array($request->compliances)) {
            foreach ($request->compliances as $period => $data) {
                if (! in_array($period, Client::COMPLIANCE_PERIODS, true)) {
                    continue;
                }

                ClientCompliance::create([
                    'client_id' => $client->id,
                    'period' => $period,
                    'pph_21' => $data['pph_21'] ?? null,
                    'pph_unifikasi' => $data['pph_unifikasi'] ?? null,
                    'ppn' => $data['ppn'] ?? null,
                    'pp_55' => $data['pp_55'] ?? null,
                    'pph_25' => $data['pph_25'] ?? null,
                    'lk' => $data['lk'] ?? null,
                    'notes' => $data['notes'] ?? null,
                ]);
            }
        }

        return redirect()->route('clients.show', $client)
            ->with('status', "Client {$client->name} berhasil ditambahkan ke sistem.");
    }

    public function show(Client $client): View
    {
        $client->load(['compliances', 'projects.tasks', 'projects.threats']);
        $periods = Client::COMPLIANCE_PERIODS;
        $compliancesMap = $client->getCompliancesMap();

        return view('clients.show', compact('client', 'periods', 'compliancesMap'));
    }

    public function edit(Client $client): View
    {
        $client->load('compliances');
        $periods = Client::COMPLIANCE_PERIODS;
        $compliancesMap = $client->getCompliancesMap();
        $staffMembers = Staff::where('is_active', true)->orderBy('name')->get();

        return view('clients.edit', compact('client', 'periods', 'compliancesMap', 'staffMembers'));
    }

    public function update(Request $request, Client $client): RedirectResponse
    {
        $validated = $request->validate([
            'client_code' => ['nullable', 'string', 'max:50'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'tax_id' => ['nullable', 'string', 'max:50'],
            'migration_date' => ['nullable', 'date'],
            'client_pic' => ['nullable', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'tax_status' => ['nullable', 'string', 'max:100'],
            'business_type' => ['nullable', 'string', 'max:150'],
            'contract_status' => ['nullable', 'string', 'max:100'],
            'start_date' => ['nullable', 'date'],
            'contract_duration_months' => ['nullable', 'integer', 'min:1'],
            'end_contract_due_date' => ['nullable', 'date'],
            'client_type' => ['nullable', 'string', 'max:100'],
            'finance_package' => ['nullable', 'string', 'max:150'],
            'tax_package' => ['nullable', 'string', 'max:150'],
            'addon' => ['nullable', 'string', 'max:255'],
            'package_detail' => ['nullable', 'string'],
            'status' => ['required', 'string', 'max:50'],
            'files' => ['nullable', 'string'],
            'review_approval' => ['nullable', 'string', 'max:100'],
            'tax_pic' => ['nullable', 'string', 'max:255'],
            'accounting_pic' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'compliances' => ['nullable', 'array'],
        ]);

        $client->update($validated);

        if ($request->has('compliances') && is_array($request->compliances)) {
            foreach ($request->compliances as $period => $data) {
                if (! in_array($period, Client::COMPLIANCE_PERIODS, true)) {
                    continue;
                }

                ClientCompliance::updateOrCreate(
                    [
                        'client_id' => $client->id,
                        'period' => $period,
                    ],
                    [
                        'pph_21' => $data['pph_21'] ?? null,
                        'pph_unifikasi' => $data['pph_unifikasi'] ?? null,
                        'ppn' => $data['ppn'] ?? null,
                        'pp_55' => $data['pp_55'] ?? null,
                        'pph_25' => $data['pph_25'] ?? null,
                        'lk' => $data['lk'] ?? null,
                        'notes' => $data['notes'] ?? null,
                    ]
                );
            }
        }

        return redirect()->route('clients.show', $client)
            ->with('status', "Data client {$client->name} dan matriks kepatuhan berhasil diperbarui.");
    }

    public function destroy(Client $client): RedirectResponse
    {
        $user = auth()->user();
        if (! $user || (! $user->isBoss() && ! $user->can('delete clients'))) {
            abort(403, 'Hanya Partner (Boss) yang dapat menghapus data client.');
        }

        $clientName = $client->name;
        $client->delete();

        return redirect()->route('clients.index')
            ->with('status', "Client {$clientName} berhasil dihapus.");
    }
}
