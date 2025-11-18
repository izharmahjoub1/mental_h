<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class SetupController extends Controller
{
    public function migrate(Request $request)
    {
        try {
            // Vérifier la connexion à la base de données
            DB::connection()->getPdo();
            
            // Exécuter les migrations
            Artisan::call('migrate', ['--force' => true]);
            
            return response()->json([
                'success' => true,
                'message' => 'Migrations exécutées avec succès',
                'output' => Artisan::output(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors des migrations',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function createUsers(Request $request)
    {
        try {
            // Exécuter le script de création d'utilisateurs
            $output = [];
            exec('php ' . base_path('create-users.php'), $output);
            
            return response()->json([
                'success' => true,
                'message' => 'Utilisateurs créés avec succès',
                'output' => implode("\n", $output),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création des utilisateurs',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function health(Request $request)
    {
        try {
            DB::connection()->getPdo();
            
            return response()->json([
                'status' => 'ok',
                'database' => 'connected',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'database' => 'disconnected',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}

