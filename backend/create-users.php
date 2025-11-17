<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\Patient;
use Illuminate\Support\Facades\Hash;

// Créer un clinicien
$clinician = User::firstOrCreate(
    ['email' => 'clinicien@test.com'],
    [
        'name' => 'Dr. Test',
        'password' => Hash::make('password'),
        'role' => 'CLINICIAN'
    ]
);
echo "✓ Clinicien créé: {$clinician->email}\n";

// Créer un patient
$patientUser = User::firstOrCreate(
    ['email' => 'patient@test.com'],
    [
        'name' => 'Patient Test',
        'password' => Hash::make('password'),
        'role' => 'PATIENT'
    ]
);

$patient = Patient::firstOrCreate(
    ['user_id' => $patientUser->id],
    [
        'phone' => '0123456789',
        'date_of_birth' => '1980-01-01'
    ]
);
echo "✓ Patient créé: {$patientUser->email}\n";

echo "\n✅ Utilisateurs de test créés avec succès!\n";
echo "   Clinicien: clinicien@test.com / password\n";
echo "   Patient: patient@test.com / password\n";

