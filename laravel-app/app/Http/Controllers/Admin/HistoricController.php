<?php

namespace App\Http\Controllers\Admin;

use App\Models\Historic;
use App\Models\VirtualMachine;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class HistoricController extends Controller
{
    public function index(Request $request)
    {
        $query = Historic::with(['virtualMachine.user']);

        // Filtrage par utilisateur
        if ($request->has('user_id')) {
            $query->whereHas('virtualMachine', function($q) use ($request) {
                $q->where('user_id', $request->user_id);
            });
        }

        // Filtrage par statut
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filtrage par date
        if ($request->has('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->has('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $history = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('admin.vm-history.index', compact('history'));
    }
}
