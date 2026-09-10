<?php

namespace App\Http\Controllers;

use App\Models\Staff;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class StaffController extends Controller
{
    public function index(): View
    {
        $staff = Staff::withCount('projects')->latest()->get();

        return view('staff.index', [
            'staff' => $staff,
            'types' => Staff::TYPES,
            'totalStaffCount' => Staff::count(),
            'activeStaffCount' => Staff::where('is_active', true)->count(),
            'taxAccountingStaffCount' => Staff::whereIn('type', ['tax', 'accounting'])->count(),
            'assignedStaffCount' => Staff::has('projects')->count(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        if (auth()->check() && !auth()->user()->can('manage staff') && !auth()->user()->isBoss()) {
            abort(403, 'Hanya pimpinan / admin yang diizinkan menambah data staff.');
        }

        Staff::create($this->validated($request));

        return redirect()
            ->route('staff.index')
            ->with('status', 'Staff created.');
    }

    public function update(Request $request, Staff $staff): RedirectResponse
    {
        if (auth()->check() && !auth()->user()->can('manage staff') && !auth()->user()->isBoss()) {
            abort(403, 'Hanya pimpinan / admin yang diizinkan mengubah data staff.');
        }

        $staff->update($this->validated($request, $staff));

        return redirect()
            ->route('staff.index')
            ->with('status', 'Staff updated.');
    }

    public function destroy(Staff $staff): RedirectResponse
    {
        if (auth()->check() && !auth()->user()->can('manage staff') && !auth()->user()->isBoss()) {
            abort(403, 'Hanya pimpinan / admin yang diizinkan menghapus data staff.');
        }

        $staff->delete();

        return redirect()
            ->route('staff.index')
            ->with('status', 'Staff deleted.');
    }

    private function validated(Request $request, ?Staff $staff = null): array
    {
        $id = $staff?->id ?? 'NULL';

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255', 'unique:staff,email,'.$id],
            'phone' => ['nullable', 'string', 'max:50'],
            'type' => ['required', Rule::in(Staff::TYPES)],
            'position' => ['nullable', 'string', 'max:255'],
            'is_active' => ['required', 'boolean'],
        ]);

        $validated['is_active'] = (bool) $validated['is_active'];

        return $validated;
    }
}
