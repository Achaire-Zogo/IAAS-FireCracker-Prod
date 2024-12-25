<?php

namespace App\Http\Controllers\Admin;

use App\Models\VmOffer;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class VmOfferController extends Controller
{
    public function index()
    {
        $offers = VmOffer::paginate(10);
        return view('admin.vm-offers.index', compact('offers'));
    }

    public function create()
    {
        return view('admin.vm-offers.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'cpu_count' => 'required|integer|min:1',
            'memory_size_mib' => 'required|integer|min:512',
            'disk_size_gb' => 'required|integer|min:1',
            'price_per_hour' => 'required|numeric|min:0',
            'is_active' => 'boolean'
        ]);

        VmOffer::create($validated);

        return redirect()->route('admin.vm-offers.index')
            ->with('success', 'Offre créée avec succès');
    }

    public function edit(VmOffer $vmOffer)
    {
        return view('admin.vm-offers.edit', compact('vmOffer'));
    }

    public function update(Request $request, VmOffer $vmOffer)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'cpu_count' => 'required|integer|min:1',
            'memory_size_mib' => 'required|integer|min:512',
            'disk_size_gb' => 'required|integer|min:1',
            'price_per_hour' => 'required|numeric|min:0',
            'is_active' => 'boolean'
        ]);

        $vmOffer->update($validated);

        return redirect()->route('admin.vm-offers.index')
            ->with('success', 'Offre mise à jour avec succès');
    }

    public function destroy(VmOffer $vmOffer)
    {
        $vmOffer->delete();
        return redirect()->route('admin.vm-offers.index')
            ->with('success', 'Offre supprimée avec succès');
    }
}
