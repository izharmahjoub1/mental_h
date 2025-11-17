<?php

namespace App\Services;

use App\Models\Alert;
use App\Models\Patient;
use App\Models\QuestionnaireResponse;
use App\Models\SensorReading;
use Carbon\Carbon;

class AlertService
{
    /**
     * Génère une alerte basée sur une réponse de questionnaire
     */
    public function evaluateQuestionnaireResponse(QuestionnaireResponse $response): ?Alert
    {
        $questionnaire = $response->questionnaire;
        $patient = $response->patient;
        $score = $response->total_score;

        // Exemple de logique pour PHQ-9 (0-27)
        if ($questionnaire->name === 'PHQ-9') {
            if ($score >= 20) {
                return $this->createAlert($patient, 'RED', 'questionnaire', 
                    "Score PHQ-9 critique: {$score}/27. Dépression sévère détectée.",
                    ['questionnaire_id' => $questionnaire->id, 'response_id' => $response->id]
                );
            } elseif ($score >= 15) {
                return $this->createAlert($patient, 'ORANGE', 'questionnaire',
                    "Score PHQ-9 élevé: {$score}/27. Dépression modérée.",
                    ['questionnaire_id' => $questionnaire->id, 'response_id' => $response->id]
                );
            } elseif ($score >= 10) {
                return $this->createAlert($patient, 'ORANGE', 'questionnaire',
                    "Score PHQ-9 modéré: {$score}/27. Dépression légère.",
                    ['questionnaire_id' => $questionnaire->id, 'response_id' => $response->id]
                );
            } else {
                return $this->createAlert($patient, 'GREEN', 'questionnaire',
                    "Score PHQ-9 normal: {$score}/27.",
                    ['questionnaire_id' => $questionnaire->id, 'response_id' => $response->id]
                );
            }
        }

        // Exemple pour GAD-7 (0-21)
        if ($questionnaire->name === 'GAD-7') {
            if ($score >= 15) {
                return $this->createAlert($patient, 'RED', 'questionnaire',
                    "Score GAD-7 critique: {$score}/21. Anxiété sévère.",
                    ['questionnaire_id' => $questionnaire->id, 'response_id' => $response->id]
                );
            } elseif ($score >= 10) {
                return $this->createAlert($patient, 'ORANGE', 'questionnaire',
                    "Score GAD-7 modéré: {$score}/21. Anxiété modérée.",
                    ['questionnaire_id' => $questionnaire->id, 'response_id' => $response->id]
                );
            } else {
                return $this->createAlert($patient, 'GREEN', 'questionnaire',
                    "Score GAD-7 normal: {$score}/21.",
                    ['questionnaire_id' => $questionnaire->id, 'response_id' => $response->id]
                );
            }
        }

        // Logique générique par défaut
        return $this->createAlert($patient, 'GREEN', 'questionnaire',
            "Questionnaire complété: score {$score}.",
            ['questionnaire_id' => $questionnaire->id, 'response_id' => $response->id]
        );
    }

    /**
     * Évalue une lecture de capteur et génère une alerte si nécessaire
     */
    public function evaluateSensorReading(SensorReading $reading): ?Alert
    {
        $patient = $reading->patient;
        $type = $reading->sensor_type;
        $value = (float) $reading->value;

        // Exemple: Tension artérielle
        if ($type === 'blood_pressure') {
            $parts = explode('/', $reading->value);
            $systolic = (float) ($parts[0] ?? 0);
            $diastolic = (float) ($parts[1] ?? 0);

            if ($systolic >= 180 || $diastolic >= 120) {
                return $this->createAlert($patient, 'RED', 'sensor',
                    "Tension artérielle critique: {$reading->value} {$reading->unit}",
                    ['sensor_reading_id' => $reading->id]
                );
            } elseif ($systolic >= 140 || $diastolic >= 90) {
                return $this->createAlert($patient, 'ORANGE', 'sensor',
                    "Tension artérielle élevée: {$reading->value} {$reading->unit}",
                    ['sensor_reading_id' => $reading->id]
                );
            } else {
                return $this->createAlert($patient, 'GREEN', 'sensor',
                    "Tension artérielle normale: {$reading->value} {$reading->unit}",
                    ['sensor_reading_id' => $reading->id]
                );
            }
        }

        // Exemple: Fréquence cardiaque
        if ($type === 'heart_rate') {
            if ($value >= 100 || $value < 50) {
                return $this->createAlert($patient, 'ORANGE', 'sensor',
                    "Fréquence cardiaque anormale: {$value} {$reading->unit}",
                    ['sensor_reading_id' => $reading->id]
                );
            } elseif ($value >= 120 || $value < 40) {
                return $this->createAlert($patient, 'RED', 'sensor',
                    "Fréquence cardiaque critique: {$value} {$reading->unit}",
                    ['sensor_reading_id' => $reading->id]
                );
            } else {
                return $this->createAlert($patient, 'GREEN', 'sensor',
                    "Fréquence cardiaque normale: {$value} {$reading->unit}",
                    ['sensor_reading_id' => $reading->id]
                );
            }
        }

        // Exemple: Température
        if ($type === 'temperature') {
            if ($value >= 39.0) {
                return $this->createAlert($patient, 'RED', 'sensor',
                    "Fièvre élevée: {$value} {$reading->unit}",
                    ['sensor_reading_id' => $reading->id]
                );
            } elseif ($value >= 38.0) {
                return $this->createAlert($patient, 'ORANGE', 'sensor',
                    "Fièvre modérée: {$value} {$reading->unit}",
                    ['sensor_reading_id' => $reading->id]
                );
            } else {
                return $this->createAlert($patient, 'GREEN', 'sensor',
                    "Température normale: {$value} {$reading->unit}",
                    ['sensor_reading_id' => $reading->id]
                );
            }
        }

        return null;
    }

    /**
     * Évalue les tendances récentes d'un patient (dégradation rapide)
     */
    public function evaluatePatientTrends(Patient $patient): array
    {
        $alerts = [];

        // Vérifier les dernières réponses de questionnaires (dégradation)
        $recentResponses = QuestionnaireResponse::where('patient_id', $patient->id)
            ->orderBy('created_at', 'desc')
            ->limit(3)
            ->get();

        if ($recentResponses->count() >= 2) {
            $scores = $recentResponses->pluck('total_score')->toArray();
            $trend = $scores[0] - $scores[1]; // Dernier - précédent

            if ($trend >= 5) { // Dégradation significative
                $alerts[] = $this->createAlert($patient, 'RED', 'combined',
                    "Dégradation rapide détectée: augmentation de {$trend} points sur le dernier questionnaire.",
                    ['type' => 'trend_degradation']
                );
            }
        }

        return $alerts;
    }

    /**
     * Crée une alerte
     */
    private function createAlert(
        Patient $patient,
        string $level,
        string $type,
        string $reason,
        array $sourceData = []
    ): Alert {
        return Alert::create([
            'patient_id' => $patient->id,
            'level' => $level,
            'type' => $type,
            'reason' => $reason,
            'source_data' => $sourceData,
        ]);
    }
}

