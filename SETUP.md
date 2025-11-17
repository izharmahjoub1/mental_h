# Guide de Configuration - Plateforme RPM

## Résumé de l'architecture

### Backend Laravel 11

**Migrations créées :**
- `users` - Utilisateurs avec rôles (CLINICIAN, PATIENT)
- `patients` - Profils patients
- `questionnaires` - Modèles de questionnaires (PHQ-9, GAD-7, etc.)
- `questionnaire_assignments` - Assignations de questionnaires
- `questionnaire_responses` - Réponses des patients
- `sensor_readings` - Données IoT
- `alerts` - Alertes (GREEN, ORANGE, RED)
- `messages` - Messagerie
- `personal_access_tokens` - Tokens Sanctum

**Models créés :**
- User, Patient, Questionnaire, QuestionnaireAssignment, QuestionnaireResponse
- SensorReading, Alert, Message

**Controllers API :**
- AuthController - Authentification
- PatientController - Gestion des patients
- QuestionnaireController - Questionnaires et réponses
- AlertController - Gestion des alertes
- MessageController - Messagerie
- SensorReadingController - Réception données IoT

**Services :**
- AlertService - Génération automatique d'alertes
- SensorIngestService - Normalisation des données IoT

**Policies :**
- PatientPolicy - Sécurité accès patients
- AlertPolicy - Sécurité alertes
- QuestionnairePolicy - Sécurité questionnaires

### Frontend Vue 3

**Stores Pinia :**
- useAuthStore - Authentification
- usePatientStore - Gestion patients
- useAlertStore - Gestion alertes
- useQuestionnaireStore - Questionnaires

**Composants :**
- PatientList - Liste des patients
- AlertsTable - Tableau des alertes
- QuestionnaireForm - Formulaire questionnaire
- MessagesThread - Thread de messages

**Vues Clinicien :**
- Login, Dashboard, PatientList, PatientDetails, AlertsView, MessagesView

**Vues Patient :**
- Login, Home, Questionnaires, Messages, Progress

## Étapes de configuration

### 1. Backend

```bash
cd backend
composer install
cp .env.example .env
php artisan key:generate
```

Configurer `.env` :
```
DB_CONNECTION=mysql
DB_DATABASE=rpm_db
# ... autres configs
```

```bash
php artisan migrate
php artisan serve
```

### 2. Frontend

```bash
cd frontend
npm install
npm run dev
```

### 3. Créer des utilisateurs de test

```bash
php artisan tinker
```

```php
// Clinicien
$clinician = User::create([
    'name' => 'Dr. Dupont',
    'email' => 'clinicien@test.com',
    'password' => Hash::make('password'),
    'role' => 'CLINICIAN'
]);

// Patient
$patientUser = User::create([
    'name' => 'Jean Patient',
    'email' => 'patient@test.com',
    'password' => Hash::make('password'),
    'role' => 'PATIENT'
]);

$patient = Patient::create([
    'user_id' => $patientUser->id,
    'phone' => '0123456789',
    'date_of_birth' => '1980-01-01'
]);
```

### 4. Créer un questionnaire exemple

```php
Questionnaire::create([
    'name' => 'PHQ-9',
    'title' => 'Patient Health Questionnaire-9',
    'description' => 'Questionnaire de dépression',
    'questions' => [
        ['text' => 'Peu d\'intérêt ou de plaisir à faire les choses', 'options' => [
            ['value' => 0, 'label' => 'Pas du tout'],
            ['value' => 1, 'label' => 'Plusieurs jours'],
            ['value' => 2, 'label' => 'Plus de la moitié des jours'],
            ['value' => 3, 'label' => 'Presque tous les jours']
        ]],
        // ... autres questions
    ],
    'is_active' => true
]);
```

## Test de l'API

### Connexion

```bash
curl -X POST http://localhost:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"clinicien@test.com","password":"password"}'
```

### Créer une lecture IoT

```bash
curl -X POST http://localhost:8000/api/v1/sensor-readings \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d '{
    "patient_id": 1,
    "sensor_type": "blood_pressure",
    "value": "140/90",
    "unit": "mmHg"
  }'
```

## Logique des alertes

Les alertes sont générées automatiquement :

- **GREEN** : Valeurs normales
- **ORANGE** : Valeurs modérées / tendance défavorable
- **RED** : Valeurs critiques / dégradation rapide

Seuils configurables dans `AlertService.php` :
- PHQ-9 : 0-9 (vert), 10-14 (orange), 15-19 (orange), 20+ (rouge)
- GAD-7 : 0-4 (vert), 5-9 (orange), 10-14 (orange), 15+ (rouge)
- Tension : <140/90 (vert), 140-179/90-119 (orange), ≥180/120 (rouge)

## Prochaines étapes

1. Personnaliser les seuils d'alertes selon vos besoins
2. Ajouter plus de types de questionnaires
3. Implémenter des notifications (email, SMS)
4. Ajouter des exports de données
5. Améliorer les graphiques avec plus de types de données

