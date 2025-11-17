<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SensorReading;
use App\Models\Patient;
use App\Services\SensorIngestService;
use Illuminate\Http\Request;

class SensorReadingController extends Controller
{
    public function __construct(
        private SensorIngestService $sensorIngestService
    ) {}

    /**
     * Endpoint pour recevoir des données IoT
     * Peut être appelé sans authentification si nécessaire (avec token API spécifique)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'sensor_type' => 'required|string',
            'value' => 'required',
            'unit' => 'nullable|string',
            'metadata' => 'nullable|array',
            'recorded_at' => 'nullable|date',
        ]);

        $patient = Patient::findOrFail($validated['patient_id']);

        $reading = $this->sensorIngestService->ingest($validated, $patient);

        return response()->json($reading, 201);
    }

    /**
     * Endpoint batch pour plusieurs lectures
     */
    public function storeBatch(Request $request)
    {
        $validated = $request->validate([
            'readings' => 'required|array|min:1',
            'readings.*.patient_id' => 'required|exists:patients,id',
            'readings.*.sensor_type' => 'required|string',
            'readings.*.value' => 'required',
            'readings.*.unit' => 'nullable|string',
            'readings.*.metadata' => 'nullable|array',
            'readings.*.recorded_at' => 'nullable|date',
        ]);

        $results = $this->sensorIngestService->ingestBatch($validated['readings']);

        return response()->json(['readings' => $results], 201);
    }

    /**
     * Récupérer les lectures d'un patient (clinicien ou patient lui-même)
     */
    public function index(Request $request, ?Patient $patient = null)
    {
        $user = $request->user();

        // Si patient spécifié, vérifier les permissions
        if ($patient) {
            $this->authorize('view', $patient);
            $patientId = $patient->id;
        } elseif ($user->isPatient()) {
            // Patient voit ses propres données
            $patientId = $user->patient->id ?? null;
            if (!$patientId) {
                return response()->json(['error' => 'Patient non trouvé'], 404);
            }
        } else {
            return response()->json(['error' => 'Patient requis'], 400);
        }

        $readings = SensorReading::where('patient_id', $patientId)
            ->when($request->has('sensor_type'), function($query) use ($request) {
                $query->where('sensor_type', $request->sensor_type);
            })
            ->when($request->has('from_date'), function($query) use ($request) {
                $query->where('recorded_at', '>=', $request->from_date);
            })
            ->when($request->has('to_date'), function($query) use ($request) {
                $query->where('recorded_at', '<=', $request->to_date);
            })
            ->orderBy('recorded_at', 'desc')
            ->paginate(50);

        return response()->json($readings);
    }
}

