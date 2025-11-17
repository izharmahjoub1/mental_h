<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Alert;
use App\Models\Patient;
use Illuminate\Http\Request;

class AlertController extends Controller
{
    public function index(Request $request)
    {
        // Clinicien: toutes les alertes
        // Patient: uniquement ses alertes
        if ($request->user()->isClinician()) {
            $alerts = Alert::with(['patient.user'])
                ->latest()
                ->paginate(20);
        } else {
            $patient = $request->user()->patient;
            if (!$patient) {
                return response()->json(['error' => 'Patient non trouvé'], 404);
            }
            $alerts = Alert::where('patient_id', $patient->id)
                ->latest()
                ->paginate(20);
        }

        return response()->json($alerts);
    }

    public function show(Alert $alert)
    {
        $this->authorize('view', $alert);

        $alert->load(['patient.user', 'acknowledgedBy']);

        return response()->json($alert);
    }

    /**
     * Alertes d'un patient spécifique (clinicien uniquement)
     */
    public function byPatient(Patient $patient)
    {
        $this->authorize('view', $patient);

        $alerts = Alert::where('patient_id', $patient->id)
            ->with('acknowledgedBy')
            ->latest()
            ->get();

        return response()->json($alerts);
    }

    /**
     * Marquer une alerte comme acquittée
     */
    public function acknowledge(Request $request, Alert $alert)
    {
        $this->authorize('acknowledge', $alert);

        $alert->update([
            'is_acknowledged' => true,
            'acknowledged_by' => $request->user()->id,
            'acknowledged_at' => now(),
        ]);

        return response()->json($alert);
    }

    /**
     * Statistiques des alertes pour le dashboard
     */
    public function stats(Request $request)
    {
        if (!$request->user()->isClinician()) {
            return response()->json(['error' => 'Non autorisé'], 403);
        }

        $stats = [
            'total' => Alert::count(),
            'unacknowledged' => Alert::unacknowledged()->count(),
            'by_level' => [
                'RED' => Alert::byLevel('RED')->count(),
                'ORANGE' => Alert::byLevel('ORANGE')->count(),
                'GREEN' => Alert::byLevel('GREEN')->count(),
            ],
        ];

        return response()->json($stats);
    }
}

