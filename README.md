🛒 TechNova Marketplace — Backend API
====================================

Symfony 7.3 • PostgreSQL • JWT Auth • Swagger UI • Modular Architecture

Bienvenue dans l’API officielle du projet TechNova Marketplace, une plateforme e-commerce multi‑vendeurs construite avec Symfony 7.3. Ce backend gère l’authentification, les utilisateurs, les vendeurs, les produits, les commandes et la gouvernance de la marketplace.

Sommaire
--------
- [Stack & modules clés](#stack--modules-clés)
- [Endpoints disponibles](#endpoints-disponibles)
- [Installation locale (dev)](#installation-locale-dev)
- [Authentification JWT & Postman](#authentification-jwt--postman)
- [Documentation API (Swagger)](#documentation-api-swagger)
- [Déploiement Alwaysdata (prod)](#déploiement-alwaysdata-prod)
- [Scripts utiles](#scripts-utiles)
- [Comptes de démo](#comptes-de-démo)
- [Design / UI](#design--ui)

Stack & modules clés
--------------------
- **Symfony 7.3 (full attributes)** – Architecture modulaire, domaines `User`, `Vendor`, `Product`, `Order`, …  
- **Base PostgreSQL** – Doctrine ORM 3, migrations versionnées.  
- **Authentification** – LexikJWTAuthenticationBundle (login JSON → JWT).  
- **Audit & logs** – `AuditLoggerService`, subscriber sur les succès/échecs de login, endpoints de test (`/api/test-audit`).  
- **Documentation** – NelmioApiDocBundle + Swagger UI exposé sur `/api/docs`.  
- **Sécurité** – Firewalls séparés (`/api/login`, `/api/docs`, zone `/api/**` protégée).  
- **Front tooling** – AssetMapper + Stimulus pour interfacer la doc ou l’admin.  
- **Monitoring** – Monolog JSON sur `php://stderr` en prod (Alwaysdata récupère les logs PHP).

Endpoints disponibles
---------------------
| Méthode | Route                | Description                                             | Auth |
|---------|----------------------|---------------------------------------------------------|------|
| GET     | `/api/test`          | Vérifie l’uptime de l’API (log dans monolog).           | Publique |
| GET     | `/api/test-audit`    | Génère une entrée dans `audit_log`.                     | JWT |
| GET     | `/api/me`            | Infos du user connecté (id/email).                      | JWT |
| POST    | `/api/login`         | Authentifie via email/password, renvoie JWT.            | Publique |
| GET     | `/api/products`      | Liste JSON des produits publiés (filtrage catégorie/marque). | Publique |
| GET     | `/api/products/{slug}` | Fiche produit détaillée (prix, images, avis).         | Publique |
| GET     | `/api/docs`          | Swagger UI (documentation interactive).                 | Publique (à protéger en prod) |

Pages Twig (catalogue)
----------------------
- `/` : accueil + produits récents (données issues des fixtures).
- `/catalogue` : listing avec filtres catégorie/marque.
- `/produit/{slug}` : fiche produit détaillée, images et avis.

Installation locale (dev)
-------------------------
Prérequis : PHP 8.2+, Composer 2, PostgreSQL 16, Node (facultatif pour assets).

```bash
git clone https://github.com/baptistev59/technova-backend.git
cd technova-backend
cp .env.dev .env.local         # exemple fourni pour WSL2
composer install
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
php bin/console lexik:jwt:generate-keypair
symfony serve -d               # ou php -S localhost:8000 -t public
```

> Le fichier `.env.local` peut contenir une URL Postgres locale (WSL2) et une passphrase JWT de développement.

Authentification JWT & Postman
------------------------------
1. `POST /api/login` avec JSON :
   ```json
   { "email": "user@example.com", "password": "password" }
   ```
2. Réponse :
   ```json
   { "token": "xxx.yyy.zzz" }
   ```
3. Dans Postman, ajoutez dans l’onglet **Tests** :
   ```js
   const data = pm.response.json();
   pm.collectionVariables.set("jwt_token", data.token);
   ```
4. Dans vos requêtes protégées, utilisez l’en‑tête `Authorization: Bearer {{jwt_token}}`.

Documentation API (Swagger)
---------------------------
- UI locale : <http://localhost:8000/api/docs>  
- JSON : <http://localhost:8000/api/docs.json>  
Swagger est public par défaut (firewall `docs`). Pensez à restreindre son accès en prod (auth HTTP ou IP allowlist) si les endpoints sont sensibles.

Déploiement Alwaysdata (prod)
-----------------------------
1. **Manager Alwaysdata**
   - Créez un site web pointant sur `/home/technova/www/technova-backend/public`.
   - Forcez PHP 8.2 (web + SSH) et Composer 2.
2. **Variables d’environnement** (Configuration → Variables d’environnement) :
   ```
   APP_ENV=prod
   APP_DEBUG=0
   APP_SECRET=<openssl rand -hex 32>
   DATABASE_URL=postgresql://technova:<motdepasse>@postgresql-technova.alwaysdata.net:5432/technova_api?serverVersion=16&charset=utf8
   JWT_SECRET_KEY=%kernel.project_dir%/config/jwt/private.pem
   JWT_PUBLIC_KEY=%kernel.project_dir%/config/jwt/public.pem
   JWT_PASSPHRASE=<même valeur que celle utilisée pour lexik:jwt:generate-keypair>
   CORS_ALLOW_ORIGIN=https://technova.alwaysdata.net
   MAILER_DSN=null://null
   MESSENGER_TRANSPORT_DSN=doctrine://default?auto_setup=0
   DEFAULT_URI=https://technova.alwaysdata.net
   ```
3. **Première installation via SSH** :
   ```bash
   cd ~/www
   git clone https://github.com/baptistev59/technova-backend.git
   cd technova-backend
   composer install --no-dev --optimize-autoloader
   php bin/console lexik:jwt:generate-keypair   # respectez la passphrase ci-dessus
   php bin/console doctrine:migrations:migrate --no-interaction --env=prod
   php bin/console app:create-admin --env=prod   # crée admin@test.fr ou équivalent
   ```
4. **Compilation des envs pour les workflows** :  
   Toujours sur Alwaysdata, générez le cache des variables :
   ```bash
   composer dump-env prod
   php bin/console cache:clear --env=prod --no-warmup
   ```
   Cela crée `.env.local.php` (non versionné) contenant les variables ; toutes les commandes (cron, GitHub Actions) utiliseront automatiquement les bons secrets.
5. **Automatisation GitHub Actions** (`.github/workflows/deploy-alwaysdata.yml`) :
   - Secrets requis : `SSH_REMOTE_HOST`, `SSH_REMOTE_PORT`, `SSH_REMOTE_USER`, `SSH_PRIVATE_KEY`, `DEPLOY_PATH`.
   - Le workflow rsync le code, puis exécute sur Alwaysdata :
     ```bash
     composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader
     php bin/console lexik:jwt:generate-keypair --no-interaction    # génère si absent
     php bin/console doctrine:migrations:migrate --no-interaction --env=prod
     php bin/console cache:clear --env=prod --no-warmup
     ```
   - Grâce à `composer dump-env prod`, les commandes voient `DATABASE_URL` et `JWT_*` sans avoir à exporter les variables dans le workflow.

Scripts utiles
--------------
- `php bin/console app:create-admin` – Create/update admin interactif.
- `php bin/console doctrine:fixtures:load` – (quand des fixtures seront ajoutées).
- `php bin/console make:migration` – Génère les migrations lors des évolutions du schéma.
- `php bin/console cache:clear --env=prod --no-warmup` – À utiliser après toute modification de config en prod.

Bonnes pratiques / sécurité
---------------------------
- Ne versionnez jamais `config/jwt/*.pem` ni `.env.local.php`.  
- Après chaque changement de passphrase, régénérez les clés :  
  `rm config/jwt/*.pem && php bin/console lexik:jwt:generate-keypair`.  
- Swagger étant public, pensez à activer une protection HTTP Basic sur Alwaysdata.  
- Monitorer `~/logs/php-*.log` sur Alwaysdata pour diagnostiquer les 500.  
- Les endpoints `/api/test*` peuvent être désactivés en prod (feature flag) via un firewall si nécessaire.

Design / UI
-----------
- Maquettes (Figma/PDF) : `docs/maquettes.pdf`
- Synthèse palette/typo/composants : `docs/design-system.md`
- Pages Twig alignées sur ces maquettes : `/`, `/catalogue`, `/produit/{slug}`
- **Assets locaux** : toutes les illustrations/placeholder sont versionnées dans `public/assets/images/` pour éviter les liens externes (logo, hero, pictos catégories, visuels produits).
- **Commentaires Twig** : chaque template (`templates/catalog/*.html.twig` + `templates/base.html.twig`) contient des commentaires en français qui servent de pense-bête pour se rappeler le rôle des sections (utile pour la soutenance).

Comptes de démo
---------------
- Les fixtures injectent un admin et dix comptes vendeurs. Les identifiants/mots de passe sont listés dans `docs/fixtures-users.md`.

🚀 Bon déploiement !
--------------------
Pour toute question ou pour la soutenance, suivez également le journal `docs/DEPLOYMENT_ALWAYS_DATA.md` qui retrace toutes les actions réalisées (nettoyage des clés, génération des envs, résolution d’incidents, etc.).
