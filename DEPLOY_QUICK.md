# Déploiement Rapide - Guide Simplifié

## 🚀 Option la plus rapide : Railway + Vercel (Gratuit)

### Étape 1 : Préparer le projet Git

```bash
cd /Users/izharmahjoub/mental_h
git init
git add .
git commit -m "Initial commit"
git remote add origin https://github.com/VOTRE_USERNAME/mental_h.git
git push -u origin main
```

### Étape 2 : Déployer le Backend sur Railway

1. Allez sur https://railway.app
2. Créez un compte (gratuit avec GitHub)
3. Cliquez sur "New Project" → "Deploy from GitHub repo"
4. Sélectionnez votre repo `mental_h`
5. Railway détectera automatiquement Laravel
6. Ajoutez PostgreSQL comme service
7. Dans les variables d'environnement, ajoutez :
   ```
   APP_ENV=production
   APP_DEBUG=false
   APP_KEY= (générez avec: php artisan key:generate --show en local)
   ```
8. Railway générera automatiquement les variables DB_*
9. Dans le terminal Railway, exécutez :
   ```bash
   php artisan migrate
   php artisan db:seed  # Si vous avez des seeders
   ```

### Étape 3 : Déployer le Frontend sur Vercel

1. Allez sur https://vercel.com
2. Créez un compte (gratuit avec GitHub)
3. Cliquez sur "Add New Project"
4. Importez votre repo GitHub
5. Configuration :
   - **Framework Preset** : Vite
   - **Root Directory** : `frontend`
   - **Build Command** : `npm run build`
   - **Output Directory** : `dist`
6. Variables d'environnement :
   ```
   VITE_API_URL=https://votre-projet.railway.app/api/v1
   ```
   (Remplacez par l'URL de votre backend Railway)

7. Cliquez sur "Deploy"

### Étape 4 : Configurer CORS dans le Backend

Dans `backend/config/cors.php`, assurez-vous que :
```php
'allowed_origins' => ['*'], // Ou spécifiez votre domaine Vercel
'allowed_methods' => ['*'],
'allowed_headers' => ['*'],
```

## 🔧 Alternative : ngrok pour tester en local rapidement

Si vous voulez juste tester rapidement sans déployer :

```bash
# Terminal 1 - Backend
cd backend
php artisan serve --port=8000

# Terminal 2 - ngrok pour backend
ngrok http 8000
# Copiez l'URL HTTPS (ex: https://abc123.ngrok.io)

# Terminal 3 - Frontend
cd frontend
# Créer .env.local
echo "VITE_API_URL=https://abc123.ngrok.io/api/v1" > .env.local
npm run dev

# Terminal 4 - ngrok pour frontend (optionnel)
ngrok http 3000
```

## 📝 Checklist avant déploiement

- [ ] Backend : `.env` configuré avec les bonnes valeurs
- [ ] Backend : `APP_KEY` généré
- [ ] Backend : Migrations exécutées
- [ ] Frontend : Variable `VITE_API_URL` configurée
- [ ] CORS configuré dans le backend
- [ ] Base de données créée et accessible
- [ ] Utilisateurs de test créés

## 🎯 URLs après déploiement

- **Frontend** : https://votre-projet.vercel.app
- **Backend API** : https://votre-projet.railway.app/api/v1

## ⚠️ Notes importantes

1. **Railway** : Gratuit avec 500 heures/mois, puis payant
2. **Vercel** : Gratuit pour les projets personnels
3. **Base de données** : Railway fournit PostgreSQL gratuitement
4. **HTTPS** : Automatique sur Railway et Vercel
5. **Variables d'environnement** : Ne jamais commiter le `.env`

