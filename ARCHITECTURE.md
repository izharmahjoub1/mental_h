# Architecture - Plateforme Remote Patient Monitoring (RPM)

## Vue d'ensemble

Plateforme de suivi médical à distance avec :
- **Backend** : Laravel 11 (API REST)
- **Frontend** : Vue 3 + Vite + Pinia + Vue Router

## Structure du projet

```
mental_h/
├── backend/              # Laravel 11 API
│   ├── app/
│   │   ├── Http/
│   │   │   ├── Controllers/
│   │   │   │   ├── Api/
│   │   │   │   │   ├── AuthController.php
│   │   │   │   │   ├── PatientController.php
│   │   │   │   │   ├── QuestionnaireController.php
│   │   │   │   │   ├── AlertController.php
│   │   │   │   │   ├── MessageController.php
│   │   │   │   │   └── SensorReadingController.php
│   │   │   ├── Middleware/
│   │   │   └── Policies/
│   │   ├── Models/
│   │   ├── Services/
│   │   │   ├── AlertService.php
│   │   │   └── SensorIngestService.php
│   │   └── Enums/
│   ├── database/
│   │   └── migrations/
│   └── routes/
│       └── api.php
│
└── frontend/            # Vue 3 SPA
    ├── src/
    │   ├── components/
    │   ├── views/
    │   ├── stores/
    │   ├── router/
    │   ├── services/
    │   └── App.vue
    └── vite.config.js
```

## Entités principales

### Backend
- **User** : Authentification (rôles CLINICIAN, PATIENT)
- **Patient** : Profil patient lié à User
- **Questionnaire** : Modèles de questionnaires (PHQ-9, GAD-7, etc.)
- **QuestionnaireResponse** : Réponses des patients
- **SensorReading** : Données IoT (type, valeur, timestamp)
- **Alert** : Alertes (GREEN, ORANGE, RED)
- **Message** : Messagerie clinicien ↔ patient

### Flux de données

1. **Patient** soumet questionnaire → **QuestionnaireResponse**
2. **IoT** envoie données → **SensorReading**
3. **AlertService** analyse données → génère **Alert**
4. **Clinicien** consulte dashboard → voit patients, alertes, messages

## Sécurité

- Authentification : Laravel Sanctum (tokens)
- Autorisation : Policies par rôle (CLINICIAN vs PATIENT)
- Middleware : `auth:sanctum` sur toutes les routes API

## API Endpoints

### Auth
- `POST /api/v1/auth/login`
- `POST /api/v1/auth/logout`
- `GET /api/v1/auth/me`

### Patients (Clinicien uniquement)
- `GET /api/v1/patients`
- `GET /api/v1/patients/{id}`
- `POST /api/v1/patients`
- `PUT /api/v1/patients/{id}`

### Questionnaires
- `GET /api/v1/questionnaires`
- `POST /api/v1/questionnaires/{id}/assign` (clinicien)
- `GET /api/v1/questionnaires/assigned` (patient)
- `POST /api/v1/questionnaires/{id}/responses` (patient)

### Sensor Readings
- `POST /api/v1/sensor-readings` (IoT endpoint)

### Alerts
- `GET /api/v1/alerts` (clinicien)
- `GET /api/v1/alerts/patient/{patientId}`

### Messages
- `GET /api/v1/messages`
- `POST /api/v1/messages`
- `GET /api/v1/messages/thread/{userId}`

## Frontend Routes

### Clinicien
- `/login`
- `/dashboard`
- `/patients`
- `/patients/:id`
- `/alerts`
- `/messages`

### Patient
- `/patient/login`
- `/patient/home`
- `/patient/questionnaires`
- `/patient/messages`
- `/patient/progress`

