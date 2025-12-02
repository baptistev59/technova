# Comptes créés par les fixtures

Les données de démonstration injectées via `php bin/console doctrine:fixtures:load --purge-with-truncate` créent les utilisateurs suivants :

| Rôle | Email | Mot de passe | Description |
|------|-------|--------------|-------------|
| Admin | `admin@test.fr` | `123456` | Accès complet API/Twig |
| Vendeur | `vendor01@technova.test` | `Vendor#01` | Aurora Labs |
| Vendeur | `vendor02@technova.test` | `Vendor#02` | Pulse Ride Collective |
| Vendeur | `vendor03@technova.test` | `Vendor#03` | Nexa Studio |
| Vendeur | `vendor04@technova.test` | `Vendor#04` | Lumina Habitat |
| Vendeur | `vendor05@technova.test` | `Vendor#05` | Orbit Care Robotics |
| Vendeur | `vendor06@technova.test` | `Vendor#06` | Flux Visionary |
| Vendeur | `vendor07@technova.test` | `Vendor#07` | Solara Motion |
| Vendeur | `vendor08@technova.test` | `Vendor#08` | Quantum Ring Labs |
| Vendeur | `vendor09@technova.test` | `Vendor#09` | Helios Drones |
| Vendeur | `vendor10@technova.test` | `Vendor#10` | Axon Dynamics |

> ⚠️ Le chargement des fixtures est **destructif** (`--purge-with-truncate`). Lance la commande uniquement lorsque tu souhaites repartir d’une base propre.
