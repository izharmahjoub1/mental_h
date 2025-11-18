# Dockerfile pour Laravel 11 sur Google Cloud Run
FROM php:8.2-cli-alpine

# Installer les dépendances système
RUN apk add --no-cache \
    git \
    curl \
    libpng-dev \
    libzip-dev \
    zip \
    unzip \
    postgresql-dev \
    oniguruma-dev

# Installer les extensions PHP
RUN docker-php-ext-install \
    pdo \
    pdo_pgsql \
    pdo_mysql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    zip

# Installer Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Définir le répertoire de travail
WORKDIR /var/www/html

# Copier les fichiers de dépendances
COPY composer.json composer.lock ./

# Installer les dépendances PHP (sans exécuter les scripts qui nécessitent artisan)
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-scripts

# Copier le reste de l'application
COPY . .

# Exécuter les scripts Composer maintenant que artisan est disponible
RUN composer dump-autoload --optimize --no-interaction

# Créer les répertoires nécessaires
RUN mkdir -p storage/framework/{sessions,views,cache} \
    && mkdir -p storage/logs \
    && chmod -R 775 storage bootstrap/cache

# Générer la clé d'application (sera écrasée par la variable d'environnement)
RUN php artisan key:generate --force || true

# Exposer le port (Cloud Run utilise la variable PORT)
EXPOSE 8080

# Script de démarrage
COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8080"]

