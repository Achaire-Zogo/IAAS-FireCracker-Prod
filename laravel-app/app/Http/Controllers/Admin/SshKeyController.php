<?php

namespace App\Http\Controllers\Admin;

use App\Models\SshKey;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class SshKeyController extends Controller
{
    public function index()
    {
        $sshKeys = SshKey::with('user')->paginate(10);
        return view('admin.ssh-keys.index', compact('sshKeys'));
    }

    public function show(SshKey $sshKey)
    {
        return view('admin.ssh-keys.show', compact('sshKey'));
    }

    public function destroy(SshKey $sshKey)
    {
        $sshKey->delete();
        return redirect()->route('admin.ssh-keys.index')
            ->with('success', 'Clé SSH supprimée avec succès');
    }
}
