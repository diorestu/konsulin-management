<?php

namespace App\Http\Controllers;

use App\Models\WebsiteContent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WebsiteContentController extends Controller
{
    public function index(): View
    {
        return view('website-content.index', [
            'contents' => WebsiteContent::orderBy('sort_order')->orderBy('label')->get(),
        ]);
    }

    public function update(Request $request, WebsiteContent $websiteContent): RedirectResponse
    {
        if (auth()->check() && !auth()->user()->can('manage website-content') && !auth()->user()->isBoss()) {
            abort(403, 'Hanya pimpinan / admin yang diizinkan mengubah konten website.');
        }

        $validated = $request->validate([
            'title' => ['nullable', 'string', 'max:255'],
            'body' => ['nullable', 'string', 'max:10000'],
            'button_text' => ['nullable', 'string', 'max:80'],
            'button_url' => ['nullable', 'string', 'max:255'],
            'is_published' => ['nullable', 'boolean'],
        ]);

        $websiteContent->update($validated + ['is_published' => $request->boolean('is_published')]);

        return to_route('website-content.index')->with('status', $websiteContent->label.' updated.');
    }
}
