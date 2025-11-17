# 🔧 Configuration API pour Vercel (Production)

## ❌ Problème

Le frontend déployé sur Vercel essaie d'appeler l'API sur Vercel (`https://mental-h-ashy.vercel.app/api/v1/auth/login`), mais l'API est hébergée sur Railway.

**Erreur :** `405 Method Not Allowed` car Vercel ne peut pas router les requêtes API vers Railway.

## ✅ Solution

Configurer la variable d'environnement `VITE_API_URL` dans Vercel pour pointer vers votre backend Railway.

## 🚀 Étapes de configuration

### 1. Trouver l'URL de votre backend Railway

1. Allez sur https://railway.app
2. Ouvrez votre projet backend
3. Cliquez sur votre service
4. Allez dans l'onglet **Settings**
5. Trouvez **Domains** ou **Public Domain**
6. Copiez l'URL (ex: `https://votre-projet.railway.app`)

### 2. Configurer la variable d'environnement dans Vercel

1. Allez sur https://vercel.com
2. Ouvrez votre projet `mental-h`
3. Allez dans **Settings**
4. Cliquez sur **Environment Variables**
5. Ajoutez une nouvelle variable :
   - **Name :** `VITE_API_URL`
   - **Value :** `https://VOTRE-URL-RAILWAY.railway.app`
   - **Environments :** Cochez **Production**, **Preview**, et **Development**
6. Cliquez sur **Save**

### 3. Redéployer sur Vercel

Après avoir ajouté la variable d'environnement :

1. Allez dans l'onglet **Deployments**
2. Cliquez sur les **3 points** (⋯) du dernier déploiement
3. Cliquez sur **Redeploy**
4. Ou poussez un nouveau commit sur GitHub

## 📋 Exemple de configuration

**Variable d'environnement Vercel :**
```
VITE_API_URL=https://mental-h-production.railway.app
```

**Résultat :**
- En développement local : utilise le proxy Vite (`/api/v1`)
- En production Vercel : utilise `https://mental-h-production.railway.app/api/v1`

## 🔍 Vérification

Après le redéploiement :

1. Ouvrez https://mental-h-ashy.vercel.app/login
2. Ouvrez la console du navigateur (F12)
3. Essayez de vous connecter
4. Dans l'onglet **Network**, vérifiez que la requête va vers Railway :
   - ✅ `https://votre-projet.railway.app/api/v1/auth/login`
   - ❌ `https://mental-h-ashy.vercel.app/api/v1/auth/login`

## 🛠️ Code modifié

Le fichier `frontend/src/services/apiClient.js` a été modifié pour :

1. Détecter la variable d'environnement `VITE_API_URL`
2. Utiliser l'URL complète de Railway en production
3. Utiliser le proxy local en développement

```js
const getBaseURL = () => {
  if (import.meta.env.VITE_API_URL) {
    return `${import.meta.env.VITE_API_URL}/api/v1`
  }
  return '/api/v1'
}
```

## ⚠️ Important

- Les variables d'environnement Vite doivent commencer par `VITE_`
- Après avoir ajouté/modifié une variable, **redéployez** sur Vercel
- Vérifiez que l'URL Railway est correcte et accessible

## 🐛 Dépannage

### L'erreur 405 persiste

1. Vérifiez que la variable `VITE_API_URL` est bien définie dans Vercel
2. Vérifiez que l'URL Railway est correcte (testez-la dans le navigateur)
3. Vérifiez que le backend Railway accepte les requêtes CORS depuis Vercel

### Erreur CORS

Si vous avez une erreur CORS, vérifiez la configuration CORS dans Laravel :

```php
// backend/config/cors.php
'allowed_origins' => ['*'], // Ou spécifiez votre domaine Vercel
```

### L'API ne répond pas

1. Vérifiez que Railway est en ligne
2. Testez l'URL directement : `https://votre-projet.railway.app/api/v1/auth/login`
3. Vérifiez les logs Railway pour voir les erreurs

