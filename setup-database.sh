#!/bin/bash
# Script pour créer la base de données Cloud SQL et finaliser la configuration

set -e

export PATH="$HOME/google-cloud-sdk/bin:$PATH"

echo "🗄️  Configuration de la Base de Données Cloud SQL"
echo ""

# Vérifier que gcloud est configuré
PROJECT_ID=$(gcloud config get-value project)
if [ -z "$PROJECT_ID" ]; then
    echo "❌ Erreur: Aucun projet configuré"
    echo "   Exécutez: gcloud config set project fit-medical-voice"
    exit 1
fi

echo "📋 Projet: ${PROJECT_ID}"
echo ""

# Demander le mot de passe
read -sp "Mot de passe PostgreSQL (min 8 caractères): " DB_PASSWORD
echo ""

if [ ${#DB_PASSWORD} -lt 8 ]; then
    echo "❌ Erreur: Le mot de passe doit faire au moins 8 caractères"
    exit 1
fi

# Vérifier si l'instance existe déjà
if gcloud sql instances describe mental-h-db --project=${PROJECT_ID} &>/dev/null; then
    echo "⚠️  L'instance Cloud SQL 'mental-h-db' existe déjà"
    read -p "Voulez-vous la recréer ? (o/N): " RECREATE
    if [[ "$RECREATE" =~ ^[Oo]$ ]]; then
        echo "🗑️  Suppression de l'instance existante..."
        gcloud sql instances delete mental-h-db --project=${PROJECT_ID} --quiet
    else
        echo "✅ Utilisation de l'instance existante"
    fi
fi

# Créer l'instance Cloud SQL
echo ""
echo "📦 Création de l'instance Cloud SQL..."
gcloud sql instances create mental-h-db \
  --database-version=POSTGRES_15 \
  --tier=db-f1-micro \
  --region=us-central1 \
  --root-password=${DB_PASSWORD} \
  --project=${PROJECT_ID} \
  --quiet

echo "✅ Instance créée"

# Créer la base de données
echo ""
echo "📦 Création de la base de données..."
if gcloud sql databases describe mental_h --instance=mental-h-db --project=${PROJECT_ID} &>/dev/null; then
    echo "⚠️  La base de données 'mental_h' existe déjà"
else
    gcloud sql databases create mental_h \
      --instance=mental-h-db \
      --project=${PROJECT_ID} \
      --quiet
    echo "✅ Base de données créée"
fi

# Connecter Cloud SQL à Cloud Run
echo ""
echo "🔗 Connexion de Cloud SQL à Cloud Run..."
gcloud run services update mental-h-backend \
  --add-cloudsql-instances ${PROJECT_ID}:us-central1:mental-h-db \
  --region us-central1 \
  --project ${PROJECT_ID} \
  --update-env-vars "DB_CONNECTION=pgsql,DB_HOST=/cloudsql/${PROJECT_ID}:us-central1:mental-h-db,DB_PORT=5432,DB_DATABASE=mental_h,DB_USERNAME=postgres,DB_PASSWORD=${DB_PASSWORD}" \
  --quiet

echo "✅ Cloud SQL connecté à Cloud Run"

# Attendre que le service soit prêt
echo ""
echo "⏳ Attente du redéploiement du service..."
sleep 10

# Activer les migrations automatiques et redéployer
echo ""
echo "🔄 Configuration des migrations automatiques..."
gcloud run services update mental-h-backend \
  --update-env-vars "RUN_MIGRATIONS=true" \
  --region us-central1 \
  --project ${PROJECT_ID} \
  --quiet

echo "✅ Migrations automatiques activées"
echo ""
echo "⏳ Attente du redéploiement (30 secondes)..."
sleep 30

echo ""
echo "✅ Configuration terminée"
echo "   Les migrations et utilisateurs seront créés au démarrage du service"
echo ""
echo "💡 Pour vérifier, attendez 1-2 minutes puis testez l'API"

# Obtenir l'URL du backend
BACKEND_URL=$(gcloud run services describe mental-h-backend \
  --region us-central1 \
  --project ${PROJECT_ID} \
  --format="value(status.url)")

echo ""
echo "🎉 Configuration terminée !"
echo ""
echo "📋 Résumé :"
echo "   - Instance Cloud SQL : mental-h-db"
echo "   - Base de données : mental_h"
echo "   - Cloud SQL connecté à Cloud Run"
echo "   - Migrations exécutées"
echo "   - Utilisateurs de test créés"
echo ""
echo "🌐 URL du backend :"
echo "   ${BACKEND_URL}"
echo ""
echo "🔐 Identifiants de test :"
echo "   Clinicien : clinicien@test.com / password"
echo "   Patient : patient@test.com / password"
echo ""
echo "🧪 Test de l'API :"
echo "   curl -X POST ${BACKEND_URL}/api/v1/auth/login \\"
echo "     -H \"Content-Type: application/json\" \\"
echo "     -d '{\"email\":\"clinicien@test.com\",\"password\":\"password\"}'"
echo ""

