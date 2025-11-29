# TechNova – Alwaysdata Operations Log

Ce document synthétise toutes les manipulations réalisées pour préparer et déployer l’API sur Alwaysdata. Mets-le à jour à chaque action importante pour disposer d’un journal de bord lors de ta soutenance.

---

## 1. Nettoyage du dépôt (local)
- Suppression des clés JWT versionnées (`config/jwt/private.pem`, `config/jwt/public.pem`) et des scripts de debug `public/info.php`, `public/env.php`.
- Mise à jour de `.gitignore` pour exclure toutes les clés (`*.pem`, `*.key`, `*.crt`).
- Ajout d’un README bilingue dans `config/jwt/` rappelant d’exécuter `php bin/console lexik:jwt:generate-keypair`.

## 2. Configuration Alwaysdata – Variables d’environnement
- Valeurs saisies via *Configuration → Variables d’environnement* :
  - `APP_ENV=prod`, `APP_DEBUG=0`
  - `APP_SECRET` = généré via `openssl rand -hex 32`
  - `DATABASE_URL=postgresql://technova:******@postgresql-technova.alwaysdata.net:5432/technova_api?serverVersion=16&charset=utf8`
  - `JWT_SECRET_KEY=%kernel.project_dir%/config/jwt/private.pem`
  - `JWT_PUBLIC_KEY=%kernel.project_dir%/config/jwt/public.pem`
  - `JWT_PASSPHRASE` = seconde valeur `openssl rand -hex 32`
  - `CORS_ALLOW_ORIGIN=https://technova.alwaysdata.net`
  - `MAILER_DSN=null://null`
  - `MESSENGER_TRANSPORT_DSN=doctrine://default?auto_setup=0`
  - **À ajouter** : `DEFAULT_URI=https://technova.alwaysdata.net` (manquant lors du `cache:clear`, voir §4).

## 3. Installation sur l’hébergement via SSH
```bash
cd ~/www/technova-backend
composer install --no-dev --optimize-autoloader
php bin/console lexik:jwt:generate-keypair
php bin/console doctrine:migrations:migrate --no-interaction --env=prod
php bin/console app:create-admin --env=prod
```

## 4. Incidents rencontrés & résolutions
| Date | Commande | Symptôme | Résolution / Statut |
|------|----------|----------|---------------------|
| 29/11 | `composer install --no-dev --optimize-autoloader` | `Environment variable not found: "DEFAULT_URI"` + warning Doctrine `report_fields_where_declared` | Ajouter `DEFAULT_URI` dans les variables Alwaysdata (ex. `https://technova.alwaysdata.net`). Warning Doctrine simplement informatif. **À faire** tant que la variable n’est pas créée. |
| 29/11 | `php bin/console lexik:jwt:generate-keypair` puis `--overwrite` | Message “Your keys already exist” lors de la première tentative | Résolu : exécution avec `--overwrite` a généré une nouvelle paire de clés sur Alwaysdata. |
| 29/11 | `php bin/console doctrine:migrations:migrate --env=prod` | `The version "latest" couldn't be reached, there are no registered migrations.` | À investiguer après résolution de `DEFAULT_URI`. Vérifier que les fichiers du dossier `migrations/` sont bien déployés sur Alwaysdata (git pull), puis relancer la commande. |
| 29/11 | `composer install --no-dev --optimize-autoloader` | `ClassNotFoundError` sur `DebugBundle` car le cache est vidé en mode `dev` alors que les dépendances `dev` sont absentes | Assurer que les commandes sont exécutées avec `APP_ENV=prod APP_DEBUG=0` (ex: `APP_ENV=prod APP_DEBUG=0 composer install ...`). |
| 29/11 | `php bin/console doctrine:migrations:migrate --env=prod` | Connexion PostgreSQL vers `127.0.0.1:5432` refusée | Les variables Alwaysdata ne sont pas injectées dans la session SSH par défaut. Exporter les variables (ex. `export DATABASE_URL="postgresql://..."`) ou créer un `.env.local` sur le serveur avant d’exécuter les commandes. |
| 29/11 | Création de `.env.local` sur Alwaysdata | Besoin de forcer APP_ENV=prod et le DSN Postgres Alwaysdata pour toutes les commandes CLI | Fichier ajouté manuellement sur le serveur avec `APP_ENV=prod`, `APP_DEBUG=0`, `DATABASE_URL=postgresql://technova:***@postgresql-technova.alwaysdata.net:5432/technova_api?...`, `DEFAULT_URI`, `MAILER_DSN`, `MESSENGER_TRANSPORT_DSN`, etc. |
| 29/11 | `php bin/console doctrine:database:drop/create` | `permission denied to create database` | Alwaysdata interdit la création de base via CLI. Solution : créer/supprimer la base `technova_api` depuis le manager, puis relancer les commandes Symfony. |
| 29/11 | `php bin/console doctrine:migrations:migrate --env=prod` (après recréation via manager) | `relation "address" already exists` | Le schéma contenait encore les tables. Vider le schéma `public` depuis le manager (ou `DROP TABLE ... CASCADE`) avant de relancer la migration. Supprimer les migrations dupliquées côté serveur (ex. `Version20251128105214.php`). |

## 5. Prochaines actions
1. **Variables** : déclarer `DEFAULT_URI=https://technova.alwaysdata.net` dans Alwaysdata, vérifier les valeurs d’`APP_SECRET` et `JWT_PASSPHRASE`.
2. **Installation** : relancer `composer install --no-dev --optimize-autoloader` (devrait passer une fois la variable ajoutée).
3. **Migrations** : le dossier `~/www/technova-backend/migrations/` est vide sur Alwaysdata (voir commande `ls migrations/`). Vérifier que les fichiers sont bien suivis en Git localement puis pousser/puller depuis le serveur (`git pull origin master`). Sur base neuve, nettoyer le schéma via le manager si des tables subsistent avant d’exécuter `php bin/console doctrine:migrations:migrate --no-interaction --env=prod`. Supprimer toute migration dupliquée directement sur le serveur.
4. **Provisioning** : lancer `php bin/console app:create-admin --env=prod`. ✅ (admin `admin@test.fr` créé depuis Alwaysdata, commande validée le 29/11).
5. **Tests** : valider `/api/test` et `/api/docs` sur `https://technova.alwaysdata.net` (protéger Swagger si nécessaire).

## 6. GitHub Actions – `deploy-alwaysdata.yml`
- Déclenchement : `push` sur `master`.  
- Étapes principales :
  1. `actions/checkout` récupère le code.
  2. `rsync` copie les sources vers Alwaysdata en excluant `.git`, `.github`, `var/`, `vendor/`.
  3. Via `appleboy/ssh-action`, le serveur exécute :
     ```bash
     composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader
     php bin/console lexik:jwt:generate-keypair --no-interaction # si les clés n'existent pas
     php bin/console doctrine:migrations:migrate --no-interaction --env=prod
     php bin/console cache:clear --env=prod --no-warmup
     ```
- Secrets GitHub à renseigner :
  - `SSH_REMOTE_HOST`, `SSH_REMOTE_PORT`
  - `SSH_REMOTE_USER`
  - `SSH_PRIVATE_KEY` (clé privée autorisée sur Alwaysdata)
  - `DEPLOY_PATH` (ex. `/home/technova/www/technova-backend`)
- Les variables d’environnement restent gérées par Alwaysdata (plus de `.env.local.php` généré par le workflow).

> Pense à enrichir ce fichier dès que tu réalises une nouvelle opération (tests, corrections, incidents). Ce sera ta trace pour la soutenance.
