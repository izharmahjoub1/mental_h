#!/bin/sh
set -e

# Attendre que la base de données soit prête (si nécessaire)
# Vous pouvez ajouter une vérification ici

# Exécuter les migrations si la variable RUN_MIGRATIONS est définie
if [ "$RUN_MIGRATIONS" = "true" ]; then
    echo "🔄 Exécution des migrations..."
    php artisan migrate --force || true
    echo "👥 Création des utilisateurs de test..."
    php create-users.php || true
    echo "✅ Migrations et utilisateurs créés"
fi

# Vider et recréer le cache
php artisan config:clear || true
php artisan cache:clear || true
php artisan route:clear || true
php artisan view:clear || true

# Optimiser pour la production
php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true

# Démarrer le serveur
exec "$@"

