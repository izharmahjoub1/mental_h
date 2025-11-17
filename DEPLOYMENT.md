# Guide de Déploiement - Plateforme RPM

## Options de déploiement

### Option 1 : Déploiement rapide (Recommandé pour les tests)

#### Frontend (Vercel - Gratuit)
1. **Préparer le build**
```bash
cd frontend
npm run build
```

2. **Déployer sur Vercel**
   - Allez sur https://vercel.com
   - Connectez votre compte GitHub
   - Importez le projet
   - Dossier racine : `frontend`
   - Build command : `npm run build`
   - Output directory : `dist`
   - Variables d'environnement :
     ```
     VITE_API_URL=https://votre-backend.railway.app/api/v1
     ```

#### Backend (Railway - Gratuit avec crédits)
1. **Préparer le projet**
```bash
cd backend
# Créer un fichier railway.json (optionnel)
```

2. **Déployer sur Railway**
   - Allez sur https://railway.app
   - Créez un nouveau projet
   - Connectez votre repo GitHub
   - Sélectionnez le dossier `backend`
   - Railway détectera automatiquement Laravel
   - Variables d'environnement à configurer :
     ```
     APP_ENV=production
     APP_DEBUG=false
     APP_KEY= (générer avec: php artisan key:generate --show)
     DB_CONNECTION=postgres (Railway fournit PostgreSQL)
     DB_HOST= (fourni par Railway)
     DB_DATABASE= (fourni par Railway)
     DB_USERNAME= (fourni par Railway)
     DB_PASSWORD= (fourni par Railway)
     ```

3. **Exécuter les migrations**
   - Dans Railway, ouvrez le terminal
   - Exécutez : `php artisan migrate`

### Option 2 : Déploiement complet (Render.com)

#### Backend sur Render
1. Créez un compte sur https://render.com
2. Créez un nouveau "Web Service"
3. Connectez votre repo GitHub
4. Configuration :
   - Build Command : `cd backend && composer install --no-dev --optimize-autoloader`
   - Start Command : `cd backend && php artisan serve --host=0.0.0.0 --port=$PORT`
   - Environment : PHP
   - Ajoutez PostgreSQL comme addon

#### Frontend sur Render
1. Créez un nouveau "Static Site"
2. Connectez votre repo GitHub
3. Configuration :
   - Build Command : `cd frontend && npm install && npm run build`
   - Publish Directory : `frontend/dist`
   - Environment Variables :
     ```
     VITE_API_URL=https://votre-backend.onrender.com/api/v1
     ```

### Option 3 : Déploiement manuel (VPS)

#### Prérequis
- Serveur VPS (Ubuntu 20.04+)
- Nginx
- PHP 8.2+
- Node.js 18+
- PostgreSQL ou MySQL

#### Étapes

1. **Cloner le projet**
```bash
git clone votre-repo
cd mental_h
```

2. **Backend**
```bash
cd backend
composer install --no-dev --optimize-autoloader
cp .env.example .env
php artisan key:generate
# Configurer .env avec les bonnes valeurs
php artisan migrate
php artisan config:cache
php artisan route:cache
```

3. **Frontend**
```bash
cd frontend
npm install
npm run build
# Copier dist/ vers /var/www/html ou configurer Nginx
```

4. **Configuration Nginx**
```nginx
# Backend
server {
    listen 80;
    server_name api.votre-domaine.com;
    root /chemin/vers/mental_h/backend/public;

    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }
}

# Frontend
server {
    listen 80;
    server_name votre-domaine.com;
    root /chemin/vers/mental_h/frontend/dist;

    index index.html;

    location / {
        try_files $uri $uri/ /index.html;
    }

    location /api {
        proxy_pass http://api.votre-domaine.com;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
    }
}
```

## Configuration CORS (Important)

Dans `backend/config/cors.php` ou créer un middleware :

```php
// backend/app/Http/Middleware/Cors.php
public function handle($request, Closure $next)
{
    return $next($request)
        ->header('Access-Control-Allow-Origin', '*')
        ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
        ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization');
}
```

## Variables d'environnement Frontend

Créer `frontend/.env.production` :
```
VITE_API_URL=https://votre-backend-url.com/api/v1
```

## Sécurité en production

1. **Backend**
   - `APP_DEBUG=false`
   - `APP_ENV=production`
   - Utiliser HTTPS
   - Configurer CORS correctement
   - Utiliser des tokens sécurisés

2. **Frontend**
   - Ne pas exposer les clés API
   - Utiliser HTTPS
   - Configurer les headers de sécurité

## Test rapide avec ngrok (Développement local)

```bash
# Installer ngrok
brew install ngrok  # Mac
# ou télécharger depuis https://ngrok.com

# Lancer le tunnel
ngrok http 3000  # Pour le frontend
ngrok http 8000  # Pour le backend (dans un autre terminal)

# Mettre à jour VITE_API_URL dans frontend avec l'URL ngrok du backend
```

