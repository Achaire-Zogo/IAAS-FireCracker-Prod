<?php

namespace App\Http\Controllers;

use App\Models\VirtualMachine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MetricsController extends Controller
{
    public function updateMetrics(Request $request)
    {
        try {
            // Validation des paramètres
            $validated = $request->validate([
                'user_id' => 'required|integer',
                'vm_id' => 'required|integer',
                'cpu_usage' => 'required|numeric',
                'memory_usage' => 'required|numeric',
                'disk_usage' => 'required|numeric'
            ]);

            // Connexion à la base de données configurée
            $connection = DB::connection();
            if (!$connection) {
                Log::error('Erreur de connexion à la base de données');
                return response()->json([
                    'error' => 'Erreur de connexion à la base de données'
                ], 500);
            }

            // Rechercher la VM
            $vm = VirtualMachine::where('user_id', $validated['user_id'])
                ->where('id', $validated['vm_id'])
                ->first();

            if (!$vm) {
                Log::warning('VM non trouvée', [
                    'user_id' => $validated['user_id'],
                    'vm_id' => $validated['vm_id']
                ]);
                return response()->json([
                    'error' => 'Machine virtuelle introuvable'
                ], 404);
            }

            // Mettre à jour les métriques
            $vm->cpu_usage = $validated['cpu_usage'];
            $vm->memory_usage = $validated['memory_usage'];
            $vm->disk_usage = $validated['disk_usage'];
            $vm->save();

            Log::info('Métriques mises à jour avec succès', [
                'vm_id' => $vm->id,
                'metrics' => [
                    'cpu' => $validated['cpu_usage'],
                    'memory' => $validated['memory_usage'],
                    'disk' => $validated['disk_usage']
                ]
            ]);

            return response()->json([
                'message' => 'Métriques enregistrées avec succès',
                'data' => [
                    'vm_id' => $vm->id,
                    'metrics' => [
                        'cpu' => $vm->cpu_usage,
                        'memory' => $vm->memory_usage,
                        'disk' => $vm->disk_usage
                    ]
                ]
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('Validation des métriques échouée', [
                'errors' => $e->errors()
            ]);
            return response()->json([
                'error' => 'Paramètres invalides',
                'details' => $e->errors()
            ], 400);

        } catch (\Exception $e) {
            Log::error('Erreur lors de la mise à jour des métriques', [
                'error' => $e->getMessage()
            ]);
            return response()->json([
                'error' => 'Une erreur s\'est produite lors de la mise à jour des métriques',
                'details' => $e->getMessage()
            ], 500);
        }
    }
}
