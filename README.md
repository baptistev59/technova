# 🧙‍♂️ **TechNova Backend – Installation Wizard**

[![Symfony Version](https://img.shields.io/badge/Symfony-7.3-000000?logo=symfony&logoColor=white)]()
[![PHP Version](https://img.shields.io/badge/PHP-%3E%3D%208.2-777BB4?logo=php&logoColor=white)]()
[![PostgreSQL](https://img.shields.io/badge/PostgreSQL-16-336791?logo=postgresql&logoColor=white)]()
[![License](https://img.shields.io/badge/License-MIT-green.svg)]()
[![CI Deploy](https://img.shields.io/github/actions/workflow/status/baptistev59/technova-backend/deploy-alwaysdata.yml?label=Deploy%20AlwaysData)]()
[![Stars](https://img.shields.io/github/stars/baptistev59/technova-backend?style=social)]()

TechNova intègre un **assistant d’installation complet**, permettant de configurer rapidement et proprement l’environnement du projet, aussi bien **en local** que sur un hébergement **AlwaysData / OVH / VPS**.

---

Le wizard gère automatiquement :
✔ la configuration PostgreSQL
✔ la création ou réinitialisation de la base
✔ le test de connexion
✔ la réparation des migrations
✔ l’exécution finale des migrations
✔ **la création d’un administrateur**

---

# 🚀 **Lancer l’installation**

Depuis la racine du projet :

```bash
symfony console app:install
```

L’assistant vous guide étape par étape.

---

# 📌 **Fonctionnalités du Wizard**

## ✔ Vérification de l’environnement PHP

-   PHP ≥ 8.2
-   Extensions requises (`pdo_pgsql`, `openssl`, `mbstring`…)
-   Messages d’erreur clairs

---

## ✔ Configuration interactive de la base PostgreSQL

-   Lecture automatique de `.env.local`
-   Suggestion des valeurs actuelles
-   Mise à jour automatique de `DATABASE_URL`

---

## ✔ Création / réinitialisation de la base PostgreSQL

-   Vérifie si la base existe
-   Propose de la supprimer / recréer
-   Vérifie les droits
-   Crée la base automatiquement
-   Adapté aux environnements mutualisés

---

## ✔ Test de connexion PostgreSQL

-   Connexion PDO
-   Détection : base inexistante, mauvais host, mauvais password
-   Conseils spécifiques AlwaysData

---

## ✔ Vérification / réparation automatique des migrations Doctrine

-   Test `migrate --dry-run`
-   Détection des migrations cassées
-   Suppression automatique des migrations invalides
-   Ré-génération propre
-   Continuité fiable

---

## ✔ Exécution des migrations Doctrine

-   Application automatique
-   Support du Retry
-   Messages explicites

---

## ✔ **Création de l’utilisateur administrateur (NOUVEAU)**

-   Vérifie si un admin existe déjà
-   Pose : email + mot de passe
-   Valeurs par défaut : `admin@test.com / 123456`
-   Hash du mot de passe
-   Création d’un utilisateur avec `ROLE_ADMIN`

---

## ✔ Résumé final

```
🎉 INSTALLATION TERMINÉE 🎉

- Environnement OK
- Base configurée
- Connexion testée
- Migrations réparées
- Migrations exécutées
- Administrateur créé (ou existant)
```

---

# 🏗 **Architecture du Wizard**

```
src/
└── Install/
    ├── InstallCommand.php
    ├── Util/
    │   ├── EnvReader.php
    │   └── DatabaseDsnParser.php
    └── Step/
        ├── StepInterface.php
        ├── CheckEnvironmentStep.php
        ├── ConfigureDatabaseStep.php
        ├── CreateOrResetDatabaseStep.php
        ├── TestDatabaseConnectionStep.php
        ├── RepairMigrationsStep.php
        ├── RunMigrationsStep.php
        ├── CreateAdminStep.php
        └── SummaryStep.php
```

Chaque étape est indépendante et supporte le mode Retry.

---

# 🔧 **Commandes utilitaires**

## ✔ `app:configure-database`

Reconfigurer la base manuellement
→ utile hors wizard, pas de risque de sécurité

## ✔ `app:create-admin`

Créer un admin manuellement
→ utile en développement
→ ⚠ À ne pas exécuter en production

## ❌ `app:setup`

Supprimée car elle dupliquait les étapes du wizard

---

# 🌐 **Utilisation sur AlwaysData**

## 1️⃣ Créer la base dans l’interface

-   Host : `postgresql-xxxxx.alwaysdata.net`
-   Port : `5432`
-   Base + utilisateur

## 2️⃣ SSH

```bash
ssh votrelogin@ssh-votrecompte.alwaysdata.net
```

## 3️⃣ Aller dans le dossier du projet

```bash
cd ~/www/technova-backend
```

## 4️⃣ Installer les dépendances

```bash
composer install --no-dev --optimize-autoloader
```

## 5️⃣ Lancer l’assistant

```bash
symfony console app:install
```

---

# 🎯 **Avantages du Wizard TechNova**

-   Installation complète & zéro-stress
-   Compatible local / Docker / mutualisé / VPS
-   Auto-réparation des migrations
-   Création d’un admin intégrée
-   Architecture pro et extensible
-   Parfait pour CI/CD sur AlwaysData

---

# 🧩 **Roadmap (prévue)**

-   Étape Mailer
-   Étape Clés JWT
-   Étape Vendor par défaut
-   Étape sécurité (CORS/JWT préconfiguré)
