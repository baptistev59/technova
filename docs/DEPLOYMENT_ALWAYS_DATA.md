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
| Date | Commande | Symptôme | Résolution |
|------|----------|----------|------------|
| 29/11 | `composer install --no-dev --optimize-autoloader` | `Environment variable not found: "DEFAULT_URI"` et warning Doctrine `report_fields_where_declared` | Ajouter `DEFAULT_URI` dans les variables Alwaysdata (par ex. `https://technova.alwaysdata.net`). Relancer `composer install` (le warning Doctrine est informatif tant qu’on reste sur DoctrineBundle 2.x). |
| 29/11 | `php bin/console lexik:jwt:generate-keypair` | `Your keys already exist` | Supprimer les anciens fichiers dans `/home/technova/www/technova-backend/config/jwt/` ou relancer la commande avec `--overwrite` pour générer une nouvelle paire. S’assurer que le dossier est inscriptible par PHP. |

## 5. Prochaines actions
- Après ajout de `DEFAULT_URI`, relancer `composer install --no-dev --optimize-autoloader`.
- Générer les clés JWT avec `php bin/console lexik:jwt:generate-keypair --overwrite`.
- Exécuter les migrations et créer un admin (`app:create-admin`) une fois les clés prêtes.
- Tester `/api/test` et `/api/docs` depuis `https://technova.alwaysdata.net`.

> Pense à enrichir ce fichier dès que tu réalises une nouvelle opération (tests, corrections, incidents). Ce sera ta trace pour la soutenance.
