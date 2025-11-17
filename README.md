# Plateforme Remote Patient Monitoring (RPM)

Plateforme complète de suivi médical à distance avec backend Laravel 11 et frontend Vue 3.

## Architecture

- **Backend** : Laravel 11 (API REST)
- **Frontend** : Vue 3 + Vite + Pinia + Vue Router
- **Authentification** : Laravel Sanctum
- **Base de données** : MySQL/PostgreSQL

## Structure du projet

```
mental_h/
├── backend/          # API Laravel
└── frontend/         # SPA Vue 3
```

## Installation

### Backend (Laravel)

```bash
cd backend

# Installer les dépendances
composer install

# Copier le fichier .env
cp .env.example .env

# Générer la clé d'application
php artisan key:generate

# Configurer la base de données dans .env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=rpm_db
DB_USERNAME=root
DB_PASSWORD=

# Exécuter les migrations
php artisan migrate

# Créer un utilisateur de test (seeder optionnel)
php artisan tinker
# User::create(['name' => 'Clinicien Test', 'email' => 'clinicien@test.com', 'password' => Hash::make('password'), 'role' => 'CLINICIAN']);

# Démarrer le serveur
php artisan serve
```

### Frontend (Vue 3)

```bash
cd frontend

# Installer les dépendances
npm install

# Démarrer le serveur de développement
npm run dev
```

## Configuration

### Enregistrer les Policies dans Laravel

Ajoutez dans `backend/app/Providers/AuthServiceProvider.php` :

```php
use App\Models\Patient;
use App\Policies\PatientPolicy;
use App\Models\Alert;
use App\Policies\AlertPolicy;
use App\Models\Questionnaire;
use App\Policies\QuestionnairePolicy;

protected $policies = [
    Patient::class => PatientPolicy::class,
    Alert::class => AlertPolicy::class,
    Questionnaire::class => QuestionnairePolicy::class,
];
```

### Configuration Sanctum

Assurez-vous que Sanctum est installé :

```bash
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
php artisan migrate
```

## Utilisation

### Endpoints API principaux

- `POST /api/v1/auth/login` - Connexion
- `GET /api/v1/patients` - Liste des patients (clinicien)
- `GET /api/v1/questionnaires` - Liste des questionnaires
- `POST /api/v1/questionnaires/{id}/assign` - Assigner un questionnaire
- `POST /api/v1/sensor-readings` - Recevoir des données IoT
- `GET /api/v1/alerts` - Liste des alertes

### Rôles

- **CLINICIAN** : Accès complet (dashboard, patients, alertes, messages)
- **PATIENT** : Accès limité (questionnaires, messages, évolution)

## Fonctionnalités

### Backend

- ✅ Authentification par token (Sanctum)
- ✅ CRUD Patients
- ✅ Gestion des questionnaires (PHQ-9, GAD-7, etc.)
- ✅ Réception de données IoT (capteurs)
- ✅ Génération automatique d'alertes (GREEN/ORANGE/RED)
- ✅ Messagerie clinicien ↔ patient
- ✅ Policies de sécurité par rôle

### Frontend

- ✅ Dashboard clinicien avec statistiques
- ✅ Liste et détails des patients
- ✅ Gestion des alertes avec filtres
- ✅ Interface patient pour questionnaires
- ✅ Messagerie en temps réel
- ✅ Graphiques d'évolution (Chart.js)

## Exemple de données IoT

```json
POST /api/v1/sensor-readings
{
  "patient_id": 1,
  "sensor_type": "blood_pressure",
  "value": "140/90",
  "unit": "mmHg",
  "recorded_at": "2024-01-15 10:30:00"
}
```

## Développement

### Backend

```bash
cd backend
php artisan serve  # Port 8000
```

### Frontend

```bash
cd frontend
npm run dev  # Port 3000 (proxy vers backend)
```

## Notes

- Les alertes sont générées automatiquement lors de la soumission de questionnaires ou de réception de données IoT
- Les seuils d'alerte sont configurables dans `AlertService.php`
- Les questionnaires utilisent un format JSON flexible pour les questions

