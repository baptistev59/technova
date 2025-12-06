# Roadmap Communication & Marketing

Cette feuille de route recense les idées autour des emails marketing, newsletters et mécanique d’opt-in. Les items pourront être traités lors d’un prochain sprint.

## 1. Inscription à la newsletter / opt-in RGPD
- Formulaire dédié (page blog, footer, pop-up) stockant l’adresse email, la source et la date de consentement.
- Génération d’un token de désinscription et route `/newsletter/unsubscribe/{token}`.
- Cas d’usage : cocher l’opt-in lors de l’inscription compte client + preuve de consentement.

## 2. Email de bienvenue avec code promo
- Au moment où `NewsletterSubscriber` est créé, envoyer automatiquement un email “Bienvenue” contenant un coupon (généré via le module promos).
- Template dédié + suivi (loger l’ID de commande où la promo est utilisée).

## 3. Séries d’emails / campagnes planifiées
- Commande Symfony (“newsletter:send”) capable d’envoyer un contenu HTML vers tous les abonnés actifs, avec pagination pour éviter les pics SMTP.
- Système de “campagnes” avec sujet, contenu, filtre des cibles et historique des envois.
- Prévoir l’envoi automatique post-inscription (J+7/J+30) pour relance, en différé ou via Messenger plus tard.

## 4. Gestion des désinscriptions & opt-out global
- Tous les emails marketing contiennent un lien “Se désabonner”. Lorsqu’il est utilisé, la colonne `is_subscribed` passe à `false` et l’historique (date, IP) est conservé.
- Empêcher l’envoi d’emails marketing aux contacts désinscrits (mais continuer les emails transactionnels type confirmation de commande).

## 5. Intégration éventuelle avec Brevo / GetResponse (phase 2)
- Si l’équipe veut un outil SaaS :
  - Synchroniser la base des abonnés via API (push des opt-in/out).
  - Utiliser les automatisations natives (scénarios, A/B tests).
  - Conserver un export des désinscriptions locales pour audit.
- À prioriser lorsque les campagnes deviennent plus complexes (statistiques, segmentation).

## 6. Blog & page de contenu
- Mise en place d’un module `BlogPost` (titre, slug, contenu riche, tags).
- Widget “Inscris-toi à la newsletter” sur le blog avec opt-in explicite.
- Possibilité de mettre en avant les promos/événements TechNova.

## 7. Monitoring & délivrabilité
- Ajouter un `newsletter_logs` (ou reuse `audit_log`) pour tracer chaque envoi marketing (date, campagne, statut SMTP).
- Option : alerter (notification Slack/email admin) en cas d’échec massif sur le SMTP ou d’augmentation du taux de désinscription.

## 8. Préparation pour les vendeurs
- À terme, offrir aux vendeurs la possibilité de sponsoriser une newsletter (section dédiée avec leurs produits en vitrine).
- Valider la charte graphique + process d’approbation côté TechNova.

Chaque item devra définir des US / estimations lors du sprint concerné.
