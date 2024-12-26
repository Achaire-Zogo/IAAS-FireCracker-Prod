<?php

namespace App\Http\Controllers;

use App\Models\VmOffer;
use Illuminate\Http\Request;
use App\Models\VirtualMachine;

class HomeController extends Controller
{
    public function index()
    {
        $offers = VmOffer::where('is_active', true)
            ->orderBy('cpu_count')
            ->orderBy('memory_size_mib')
            ->get();

        return view('welcome', compact('offers'));
    }

    public function cpu_metrics(Request $request)
    {
        // Logique pour calculer les métriques
        try{
            $user_id = $request->user_id;
            $vm_id = $request->vm_id;
            $cpu_usage = $request->cpu_usage;
            $memory_usage = $request->memory_usage;
            $disk_usage = $request->disk_usage;

            if (empty($user_id) || empty($vm_id) || empty($cpu_usage) || empty($memory_usage) || empty($disk_usage)) {
                return response()->json([
                    'error' => 'Les paramètres sont manquants'
                ], 400);
            }

            //Rechercher la VM
            $vm = VirtualMachine::where('user_id', $user_id)
                ->where('id', $vm_id)
                ->first();

            if (!$vm) {
                return response()->json([
                    'error' => 'Machine virtuelle introuvable'
                ], 404);
            }

            //Mettre à jour les métriques
            $vm->cpu_usage = $cpu_usage;
            $vm->memory_usage = $memory_usage;
            $vm->disk_usage = $disk_usage;
            $vm->save();

            return response()->json([
                'message' => 'Métriques enregistrées avec succès'
            ], 200);
        }catch(\Exception $e){
            return response()->json([
                'error' => 'Une erreur s\'est produite lors de la mise à jour des métriques : ' . $e->getMessage()
            ], 500);
        }
    }
}
