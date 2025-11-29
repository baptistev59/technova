## 🇬🇧 JWT key pair (not committed)

The private/public PEM files used by LexikJWT are generated per environment and **must never be tracked**.  
Generate them wherever you deploy (local, staging, Alwaysdata, etc.) with:

```bash
php bin/console lexik:jwt:generate-keypair
```

The command stores `private.pem` and `public.pem` in this folder and reuses the passphrase defined in your environment variables.

## 🇫🇷 Paire de clés JWT (non versionnée)

Les fichiers PEM (privé/public) utilisés par LexikJWT sont propres à chaque environnement et **ne doivent pas être commités**.  
Générez-les sur chaque machine ou hébergement (local, préprod, Alwaysdata, …) via :

```bash
php bin/console lexik:jwt:generate-keypair
```

Cette commande crée `private.pem` et `public.pem` dans ce dossier et utilise automatiquement la passphrase spécifiée dans vos variables d’environnement.
