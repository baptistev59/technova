🛒 TechNova Marketplace — Backend API

Symfony 7.3 • PostgreSQL • JWT Auth • Swagger UI • Modular Architecture

Bienvenue dans l’API officielle du projet TechNova Marketplace, une plateforme e-commerce multi-vendeurs professionnelle construite avec Symfony 7.3.
Cette API expose tous les endpoints nécessaires pour gérer l'authentification, les utilisateurs, les vendeurs, les produits, les commandes et l’administration de la marketplace.

🚀 Fonctionnalités principales
🔐 Authentification & Sécurité

Login via JWT (LexikJWTAuthenticationBundle)

Firewall dédié :

/api/login → public

/api/docs → public

/api/** → protégé par JWT

🧩 Architecture API moderne

Symfony 7.3 (full attributes)

Structure API propre, versionnée

Endpoints organisés par domaines (User, Vendor, Product, Order…)

📘 Documentation API

Nelmio ApiDocBundle + Swagger UI :

UI : http://localhost:8000/api/docs

JSON : http://localhost:8000/api/docs.json

Redoc (optionnel)

🗄️ Base de données PostgreSQL

Connexion via :
postgresql://technova:password@localhost:5432/technova_api

🔧 Développement optimisé

Scripts batch Windows (start/stop/restart serveur)

WSL2 + PHP 8.3 + Composer

Dossiers structurés

Tests automatiques (à venir)

📁 Structure du projet
technova-backend/
├─ config/
│  ├─ packages/
│  ├─ routes/
│  ├─ security.yaml
│  ├─ nelmio_api_doc.yaml
├─ migrations/
├─ src/
│  ├─ Controller/
│  ├─ Entity/
│  ├─ Repository/
│  ├─ Security/
│  ├─ Service/
├─ var/
├─ public/
├─ .env
└─ composer.json

🛠️ Installation & Démarrage
1️⃣ Cloner le projet
git clone https://github.com/baptistev59/technova-backend.git
cd technova-backend

2️⃣ Installer les dépendances
composer install

3️⃣ Créer la base PostgreSQL
php bin/console doctrine:database:create

4️⃣ Lancer les migrations
php bin/console doctrine:migrations:migrate

5️⃣ Générer les clés JWT
php bin/console lexik:jwt:generate-keypair

6️⃣ Démarrer le serveur Symfony
symfony serve -d


Ou via ton script Windows :

start_server.bat

🔑 Authentification JWT
Login

POST /api/login

Body attendu :

{
  "email": "user@example.com",
  "password": "password"
}


Réponse :

{
  "token": "xxx.yyy.zzz"
}


Utilisation du token dans Swagger :

Cliquez sur Authorize → Bearer Token → collez le JWT

📘 Documentation Swagger

Swagger UI
👉 http://localhost:8000/api/docs

OpenAPI JSON
👉 http://localhost:8000/api/docs.json

Swagger est totalement public (firewall configuré).

🧪 Endpoints disponibles (actuellement)
🔧 System
Method	Route	Description
GET	/api/test	Vérifie le fonctionnement général
GET	/api/test-audit	Endpoint de test AuditLog
👤 Utilisateur
Method	Route	Description
GET	/api/me	Récupère les informations du user connecté (JWT obligatoire)

☁️ Déploiement Alwaysdata / Alwaysdata deployment
🇬🇧 Steps
1. Create a web app in the Alwaysdata dashboard that points to the repo folder and set the document root to `public/`.
2. Force PHP 8.2 (both Web and SSH) and Composer 2 in the Alwaysdata environment.
3. Declare the following environment variables in *Configuration → Environment variables*:
   - `APP_ENV=prod`, `APP_DEBUG=0`, `APP_SECRET=<random 32 chars>`
   - `DATABASE_URL=postgresql://<user>:<password>@postgresql-<account>.alwaysdata.net:5432/<db>?serverVersion=16&charset=utf8`
   - `JWT_SECRET_KEY=%kernel.project_dir%/config/jwt/private.pem`
   - `JWT_PUBLIC_KEY=%kernel.project_dir%/config/jwt/public.pem`
   - `JWT_PASSPHRASE=<passphrase used by lexik:jwt:generate-keypair>`
   - `CORS_ALLOW_ORIGIN=https://<your-frontend-domain>`
   - add real DSNs for `MAILER_DSN` and `MESSENGER_TRANSPORT_DSN` if needed
4. SSH into the instance, clone the project, then run:
   ```bash
   composer install --no-dev --optimize-autoloader
   php bin/console lexik:jwt:generate-keypair
   php bin/console doctrine:migrations:migrate --no-interaction --env=prod
   php bin/console app:create-admin --env=prod
   ```
5. Ensure `/config/jwt/` stays writable, clear any leftover debug files (`public/info.php`, `public/env.php`), then reload the site. Swagger is public; protect `/api/docs` via Alwaysdata HTTP auth if the API is private.

🇫🇷 Étapes
1. Crée une application Web dans le manager Alwaysdata, cible la racine du dépôt et définis le *document root* sur `public/`.
2. Force PHP 8.2 (Web + SSH) et Composer 2 côté Alwaysdata.
3. Ajoute les variables d’environnement suivantes dans *Configuration → Variables d’environnement* :
   - `APP_ENV=prod`, `APP_DEBUG=0`, `APP_SECRET=<chaine aléatoire>`
   - `DATABASE_URL=postgresql://<user>:<motdepasse>@postgresql-<compte>.alwaysdata.net:5432/<base>?serverVersion=16&charset=utf8`
   - `JWT_SECRET_KEY=%kernel.project_dir%/config/jwt/private.pem`
   - `JWT_PUBLIC_KEY=%kernel.project_dir%/config/jwt/public.pem`
   - `JWT_PASSPHRASE=<passphrase utilisée par lexik:jwt:generate-keypair>`
   - `CORS_ALLOW_ORIGIN=https://<ton-domaine-front>`
   - configure aussi `MAILER_DSN` ou `MESSENGER_TRANSPORT_DSN` selon les besoins métiers
4. Connecte-toi en SSH, clone le projet puis exécute :
   ```bash
   composer install --no-dev --optimize-autoloader
   php bin/console lexik:jwt:generate-keypair
   php bin/console doctrine:migrations:migrate --no-interaction --env=prod
   php bin/console app:create-admin --env=prod
   ```
5. Vérifie que `/config/jwt/` est inscriptible, supprime les scripts de debug restants (`public/info.php`, `public/env.php`) et recharge le site. La doc `/api/docs` est publique par défaut : protège-la via l’auth HTTP Alwaysdata si besoin.
