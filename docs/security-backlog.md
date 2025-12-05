# Backlog sécurité & authentification

Ces sujets ont été identifiés mais seront traités dans un sprint ultérieur.

1. **Rate limiting sur `/api/login` et `/api/register`**  
   - Configurer `symfony/rate-limiter` pour limiter les tentatives (IP ou email).
   - Retourner un message explicite lorsqu’un seuil est atteint.

2. **reCAPTCHA / challenge anti-bot**  
   - Intégrer reCAPTCHA v3 ou un équivalent léger côté formulaires publics (Twig + API).
   - Bloquer la création de compte si le score est insuffisant.

3. **/api/test conditionnel**  
   - Firewall ou feature flag pour restreindre le healthcheck en production (IP allo-liste ou authentification basique).

4. **Double authentification par email**  
   - À l’issue du login, envoyer un code de confirmation (OTP) par mail.
   - Stocker l’OTP (BDD ou cache), définir une durée de validité et un compteur d’essais.
   - Exiger ce code avant de délivrer le JWT final.

5. **Rotation / invalidation des JWT**  
   - Conserver un journal des tokens émis et les invalider lors d’un logout manuel ou d’un changement critique (mot de passe).

6. **Journalisation & audit exploitables**  
   - Offrir une interface admin permettant d’afficher/exporter les entrées d’`audit_log` (filtres par date/action, export CSV).  
   - Proposer un téléchargement des logs applicatifs (`dev.log`/`stderr`) avec une vérification d’autorisation.  
   - Mettre en place une purge automatique (commande ou cron) pour supprimer anciens logs/audits au-delà d’une durée définie.

Revenir sur ce fichier avant de démarrer le sprint dédié afin d’évaluer la complexité et l’ordre de priorité.
