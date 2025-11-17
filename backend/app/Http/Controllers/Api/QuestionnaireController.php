<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Questionnaire;
use App\Models\QuestionnaireAssignment;
use App\Models\QuestionnaireResponse;
use App\Models\Patient;
use App\Services\AlertService;
use Illuminate\Http\Request;

class QuestionnaireController extends Controller
{
    public function __construct(
        private AlertService $alertService
    ) {}

    public function index(Request $request)
    {
        $questionnaires = Questionnaire::where('is_active', true)->get();

        return response()->json($questionnaires);
    }

    public function show(Questionnaire $questionnaire)
    {
        return response()->json($questionnaire);
    }

    /**
     * Assigner un questionnaire à un patient (clinicien uniquement)
     */
    public function assign(Request $request, Questionnaire $questionnaire)
    {
        $this->authorize('assign', $questionnaire);

        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'due_date' => 'nullable|date|after:today',
        ]);

        $assignment = QuestionnaireAssignment::create([
            'questionnaire_id' => $questionnaire->id,
            'patient_id' => $validated['patient_id'],
            'assigned_by' => $request->user()->id,
            'due_date' => $validated['due_date'] ?? null,
            'status' => 'PENDING',
        ]);

        return response()->json($assignment->load(['questionnaire', 'patient']), 201);
    }

    /**
     * Liste des questionnaires assignés au patient connecté
     */
    public function assigned(Request $request)
    {
        $patient = $request->user()->patient;
        
        if (!$patient) {
            return response()->json(['error' => 'Patient non trouvé'], 404);
        }

        $assignments = QuestionnaireAssignment::where('patient_id', $patient->id)
            ->where('status', 'PENDING')
            ->with('questionnaire')
            ->get();

        return response()->json($assignments);
    }

    /**
     * Soumettre une réponse à un questionnaire (patient uniquement)
     */
    public function submitResponse(Request $request, Questionnaire $questionnaire)
    {
        $patient = $request->user()->patient;
        
        if (!$patient) {
            return response()->json(['error' => 'Patient non trouvé'], 404);
        }

        $validated = $request->validate([
            'answers' => 'required|array',
            'assignment_id' => 'nullable|exists:questionnaire_assignments,id',
        ]);

        // Calcul du score (exemple simplifié)
        $totalScore = $this->calculateScore($questionnaire, $validated['answers']);

        $response = QuestionnaireResponse::create([
            'questionnaire_id' => $questionnaire->id,
            'patient_id' => $patient->id,
            'assignment_id' => $validated['assignment_id'] ?? null,
            'answers' => $validated['answers'],
            'total_score' => $totalScore,
            'interpretation' => $this->interpretScore($questionnaire, $totalScore),
        ]);

        // Mettre à jour l'assignation si applicable
        if ($validated['assignment_id']) {
            QuestionnaireAssignment::where('id', $validated['assignment_id'])
                ->update(['status' => 'COMPLETED']);
        }

        // Générer une alerte automatiquement
        $this->alertService->evaluateQuestionnaireResponse($response);

        return response()->json($response->load('questionnaire'), 201);
    }

    /**
     * Calcul simplifié du score
     */
    private function calculateScore(Questionnaire $questionnaire, array $answers): int
    {
        // Exemple: somme simple des valeurs
        return array_sum(array_values($answers));
    }

    /**
     * Interprétation du score
     */
    private function interpretScore(Questionnaire $questionnaire, int $score): string
    {
        // Logique d'interprétation basique
        if ($questionnaire->name === 'PHQ-9') {
            if ($score >= 20) return 'Dépression sévère';
            if ($score >= 15) return 'Dépression modérée';
            if ($score >= 10) return 'Dépression légère';
            return 'Minimal ou absent';
        }

        if ($questionnaire->name === 'GAD-7') {
            if ($score >= 15) return 'Anxiété sévère';
            if ($score >= 10) return 'Anxiété modérée';
            if ($score >= 5) return 'Anxiété légère';
            return 'Minimal';
        }

        return 'Score: ' . $score;
    }
}

