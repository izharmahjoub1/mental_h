<?php

namespace App\Services;

use App\Models\Patient;
use App\Models\SensorReading;
use App\Services\AlertService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class SensorIngestService
{
    public function __construct(
        private AlertService $alertService
    ) {}

    /**
     * Ingère une lecture de capteur depuis un endpoint IoT
     */
    public function ingest(array $data, ?Patient $patient = null): SensorReading
    {
        // Validation des données requises
        if (!isset($data['patient_id']) && !$patient) {
            throw new \InvalidArgumentException('Patient ID is required');
        }

        $patientId = $patient?->id ?? $data['patient_id'];
        $patient = $patient ?? Patient::findOrFail($patientId);

        // Normalisation des données
        $sensorType = $data['sensor_type'] ?? 'unknown';
        $value = $data['value'] ?? null;
        $unit = $data['unit'] ?? null;
        $recordedAt = isset($data['recorded_at']) 
            ? Carbon::parse($data['recorded_at']) 
            : now();
        $metadata = $data['metadata'] ?? [];

        // Création de la lecture
        $reading = SensorReading::create([
            'patient_id' => $patient->id,
            'sensor_type' => $sensorType,
            'value' => (string) $value,
            'unit' => $unit,
            'metadata' => $metadata,
            'recorded_at' => $recordedAt,
        ]);

        // Génération automatique d'alerte
        try {
            $this->alertService->evaluateSensorReading($reading);
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'évaluation du capteur', [
                'reading_id' => $reading->id,
                'error' => $e->getMessage()
            ]);
        }

        return $reading;
    }

    /**
     * Ingère plusieurs lectures en batch
     */
    public function ingestBatch(array $readings): array
    {
        $results = [];

        foreach ($readings as $readingData) {
            try {
                $results[] = $this->ingest($readingData);
            } catch (\Exception $e) {
                Log::error('Erreur lors de l\'ingestion batch', [
                    'data' => $readingData,
                    'error' => $e->getMessage()
                ]);
                $results[] = ['error' => $e->getMessage()];
            }
        }

        return $results;
    }

    /**
     * Normalise différents formats de données IoT
     */
    public function normalize(array $rawData): array
    {
        // Exemple de normalisation pour différents formats
        $normalized = [
            'sensor_type' => $rawData['type'] ?? $rawData['sensor_type'] ?? 'unknown',
            'value' => $rawData['value'] ?? $rawData['data'] ?? null,
            'unit' => $rawData['unit'] ?? $rawData['measurement_unit'] ?? null,
            'metadata' => $rawData['metadata'] ?? $rawData['extra'] ?? [],
        ];

        // Gestion spéciale pour la tension artérielle
        if (isset($rawData['systolic']) && isset($rawData['diastolic'])) {
            $normalized['sensor_type'] = 'blood_pressure';
            $normalized['value'] = "{$rawData['systolic']}/{$rawData['diastolic']}";
            $normalized['unit'] = 'mmHg';
        }

        return $normalized;
    }
}

