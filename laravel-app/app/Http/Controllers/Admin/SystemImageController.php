<?php

namespace App\Http\Controllers\Admin;

use App\Models\SystemImage;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class SystemImageController extends Controller
{
    public function index()
    {
        $images = SystemImage::paginate(10);
        return view('admin.system-images.index', compact('images'));
    }

    public function create()
    {
        return view('admin.system-images.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'version' => 'required|string|max:50',
            'path' => 'required|string',
            'is_active' => 'boolean'
        ]);

        SystemImage::create($validated);

        return redirect()->route('admin.system-images.index')
            ->with('success', 'Image système créée avec succès');
    }

    public function edit(SystemImage $systemImage)
    {
        return view('admin.system-images.edit', compact('systemImage'));
    }

    public function update(Request $request, SystemImage $systemImage)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'version' => 'required|string|max:50',
            'path' => 'required|string',
            'is_active' => 'boolean'
        ]);

        $systemImage->update($validated);

        return redirect()->route('admin.system-images.index')
            ->with('success', 'Image système mise à jour avec succès');
    }

    public function destroy(SystemImage $systemImage)
    {
        $systemImage->delete();
        return redirect()->route('admin.system-images.index')
            ->with('success', 'Image système supprimée avec succès');
    }
}
