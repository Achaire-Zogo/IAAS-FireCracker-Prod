<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SshKey;
use App\Models\User;
use Illuminate\Http\Request;

class SSHKeyController extends Controller
{
    public function index(Request $request)
    {
        $query = SshKey::with('user');

        // Filtrage par utilisateur
        if ($request->has('user')) {
            $query->where('user_id', $request->user);
        }

        // Recherche
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('fingerprint', 'like', "%{$search}%")
                  ->orWhereHas('user', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        $sshKeys = $query->orderBy('created_at', 'desc')->paginate(10);
        $users = User::all();

        return view('admin.ssh-keys.index', compact('sshKeys', 'users'));
    }

    public function show(SshKey $sshKey)
    {
        $sshKey->load('user');
        return view('admin.ssh-keys.show', ['key' => $sshKey]);
    }

    public function destroy(SshKey $sshKey)
    {
        try {
            // Suppression de la clé SSH
            $sshKey->delete();

            return redirect()->route('admin.ssh-keys.index')
                           ->with('success', 'Clé SSH supprimée avec succès');
        } catch (\Exception $e) {
            return redirect()->route('admin.ssh-keys.index')
                           ->with('error', 'Erreur lors de la suppression de la clé SSH : ' . $e->getMessage());
        }
    }
}
