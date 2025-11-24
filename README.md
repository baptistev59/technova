# 🧙‍♂️ Installation Wizard – TechNova

TechNova intègre un **assistant d’installation complet**, permettant de configurer rapidement et proprement l’environnement du projet, aussi bien **en local** que sur un hébergement **AlwaysData / OVH / VPS**.

Ce wizard gère automatiquement la configuration de la base de données PostgreSQL, le test de connexion et l’exécution des migrations.

---

## 🚀 Lancer l’installation

Depuis la racine du projet :

```bash
symfony console app:install
L’assistant vous guide étape par étape.

📌 Fonctionnalités du Wizard
✔ Vérification de l’environnement PHP
Version PHP (≥ 8.2)

Extensions requises (pdo_pgsql, openssl, mbstring, etc.)

Messages d’erreur explicites en cas de problème

✔ Configuration interactive de la base PostgreSQL
Lecture automatique de la configuration existante (.env.local)

Suggestion des valeurs actuelles (host, port, user…)

Possibilité de modifier ou conserver

Mise à jour automatique de DATABASE_URL

✔ Test de connexion PostgreSQL
Test PDO immédiat

Gestion intelligente des erreurs :

Mot de passe incorrect

Base inexistante

Mauvais host ou mauvais user

Messages adaptés pour les hébergements mutualisés :

« Sur AlwaysData/OVH, la base doit être créée depuis le panneau d’administration »

✔ Exécution des migrations Doctrine
Application automatique des migrations

Messages clairs en cas de migration manquante (ex : ajout de colonne sans migration)

✔ Résumé final de l’installation
En fin de wizard :

diff
Copier le code
🎉 INSTALLATION TERMINÉE 🎉

- Environnement OK
- Base configurée
- Connexion testée
- Migrations exécutées
🏗 Architecture technique du Wizard
L’assistant repose sur une architecture modulaire, professionnelle et extensible :

bash
Copier le code
src/
└── Install/
    ├── InstallCommand.php              # Orchestrateur principal (app:install)
    ├── Util/
    │   ├── EnvReader.php               # Lecture / écriture .env.local
    │   └── DatabaseDsnParser.php       # Parsing DATABASE_URL PostgreSQL
    └── Step/
        ├── StepInterface.php           # Contrat pour les étapes
        ├── CheckEnvironmentStep.php    # Étape 1
        ├── ConfigureDatabaseStep.php   # Étape 2
        ├── TestDatabaseConnectionStep.php
        ├── RunMigrationsStep.php
        └── SummaryStep.php
Chaque étape est indépendante et rejouable individuellement (retry en cas d’erreur).

🌐 Exemple d’utilisation sur AlwaysData
1️⃣ Créer la base PostgreSQL dans l’interface AlwaysData
Aller dans → Bases de données → PostgreSQL

Créer une base + un utilisateur

Noter :

Host : postgresql-votrecompte.alwaysdata.net

Port : 5432

Nom de la base

User

Password

2️⃣ Connexion au serveur
bash
Copier le code
ssh votrelogin@ssh-votrecompte.alwaysdata.net
3️⃣ Aller dans le dossier du projet
bash
Copier le code
cd ~/www/technova-backend
4️⃣ Installer les dépendances
bash
Copier le code
composer install --no-dev --optimize-autoloader
5️⃣ Lancer l’assistant
bash
Copier le code
symfony console app:install
🎯 Avantages du Wizard
Installation zéro-stress

Compatible local / Docker / hébergement mutualisé / VPS

Rejouable sans casser l’environnement

Professionnel et extensible (ajout JWT, création auto de l’admin, config mailer…)

🧩 Roadmap du Wizard (prévue)
 Étape optionnelle : création automatique d’un administrateur

 Étape optionnelle : configuration du Mailer

 Étape optionnelle : vérification du JWT (clés privées/publiques)

 Étape optionnelle : génération automatique du Vendor par défaut
