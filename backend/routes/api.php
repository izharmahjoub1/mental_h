<?php

use App\Http\Controllers\Api\AlertController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\MessageController;
use App\Http\Controllers\Api\PatientController;
use App\Http\Controllers\Api\QuestionnaireController;
use App\Http\Controllers\Api\SensorReadingController;
use App\Http\Controllers\Api\SetupController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Routes publiques
Route::prefix('v1')->group(function () {
    // Authentification
    Route::post('/auth/login', [AuthController::class, 'login']);
    
    // Setup (pour l'initialisation)
    Route::get('/setup/health', [SetupController::class, 'health']);
    Route::post('/setup/migrate', [SetupController::class, 'migrate']);
    Route::post('/setup/users', [SetupController::class, 'createUsers']);
    
    // Endpoint IoT (peut nécessiter un token API spécifique)
    Route::post('/sensor-readings', [SensorReadingController::class, 'store']);
    Route::post('/sensor-readings/batch', [SensorReadingController::class, 'storeBatch']);
});

// Routes protégées
Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    // Authentification
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me', [AuthController::class, 'me']);

    // Patients
    Route::apiResource('patients', PatientController::class);

    // Questionnaires
    Route::get('/questionnaires', [QuestionnaireController::class, 'index']);
    Route::get('/questionnaires/{questionnaire}', [QuestionnaireController::class, 'show']);
    Route::post('/questionnaires/{questionnaire}/assign', [QuestionnaireController::class, 'assign']);
    Route::get('/questionnaires/assigned/me', [QuestionnaireController::class, 'assigned']);
    Route::post('/questionnaires/{questionnaire}/responses', [QuestionnaireController::class, 'submitResponse']);

    // Sensor Readings (lectures)
    Route::get('/sensor-readings', [SensorReadingController::class, 'index']);
    Route::get('/patients/{patient}/sensor-readings', [SensorReadingController::class, 'index']);

    // Alertes
    Route::get('/alerts', [AlertController::class, 'index']);
    Route::get('/alerts/stats', [AlertController::class, 'stats']);
    Route::get('/alerts/{alert}', [AlertController::class, 'show']);
    Route::post('/alerts/{alert}/acknowledge', [AlertController::class, 'acknowledge']);
    Route::get('/patients/{patient}/alerts', [AlertController::class, 'byPatient']);

    // Messages
    Route::get('/messages', [MessageController::class, 'index']);
    Route::post('/messages', [MessageController::class, 'store']);
    Route::get('/messages/thread/{user}', [MessageController::class, 'thread']);
    Route::post('/messages/{message}/read', [MessageController::class, 'markAsRead']);
});

