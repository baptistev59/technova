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