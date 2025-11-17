<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use Illuminate\Http\Request;

class PatientController extends Controller
{
    public function index(Request $request)
    {
        // Seuls les clinicien peuvent voir tous les patients
        $this->authorize('viewAny', Patient::class);

        $patients = Patient::with(['user', 'alerts' => function($query) {
            $query->unacknowledged()->latest()->limit(5);
        }])
        ->paginate(15);

        return response()->json($patients);
    }

    public function show(Patient $patient)
    {
        $this->authorize('view', $patient);

        $patient->load([
            'user',
            'questionnaireAssignments.questionnaire',
            'questionnaireResponses.questionnaire',
            'sensorReadings' => function($query) {
                $query->latest()->limit(50);
            },
            'alerts' => function($query) {
                $query->latest()->limit(20);
            }
        ]);

        return response()->json($patient);
    }

    public function store(Request $request)
    {
        $this->authorize('create', Patient::class);

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'date_of_birth' => 'nullable|date',
            'phone' => 'nullable|string',
            'address' => 'nullable|string',
            'emergency_contact_name' => 'nullable|string',
            'emergency_contact_phone' => 'nullable|string',
            'medical_history' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $patient = Patient::create($validated);

        return response()->json($patient->load('user'), 201);
    }

    public function update(Request $request, Patient $patient)
    {
        $this->authorize('update', $patient);

        $validated = $request->validate([
            'date_of_birth' => 'nullable|date',
            'phone' => 'nullable|string',
            'address' => 'nullable|string',
            'emergency_contact_name' => 'nullable|string',
            'emergency_contact_phone' => 'nullable|string',
            'medical_history' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $patient->update($validated);

        return response()->json($patient->load('user'));
    }
}

