# Catalogue Entities Summary

- Category: hierarchical, unique slug, linked to products, **iconPath** pointe vers les SVG locaux.
- Brand: unique slug, optional logo (**logoPath**), linked to products
- Shop: belongs to a Vendor, exposes products, slug unique
- User: avatarPath optionnel (utilisé pour l’admin et les vendeurs de démo)
- Product: belongs to Category/Brand/Shop, has pricing info, featured flag, published flag
- ProductAttribute / ProductAttributeValue: définissent les attributs configurables (couleur, édition, etc.)
- ProductVariant: combinaison des valeurs avec prix/promo/stock/image propres
- ProductImage: child of Product, order/position/isMain, metadata
- ProductReview: rating/comment authored by User for Product

Repositories created for each entity, with ProductRepository featuring `findLatestPublished()` and `filterBy()`.

Fixtures (`php bin/console doctrine:fixtures:load --purge-with-truncate`) inject:
- Admin `admin@test.fr` (password `123456`)
- 10 vendeurs (`vendor01@technova.test` → `vendor10@technova.test`, mot de passe `Vendor#0X`)
- 7 catégories iconographiées, 8 marques futuristes, 10 shops
- 50 produits (5 par shop) avec images SVG locales, variantes générées automatiquement et 2 avis chacun

Pages Twig : `/`, `/catalogue`, `/produit/{slug}`. API : `/api/products`, `/api/products/{slug}`.
Next steps: styliser et brancher les endpoints panier/utilisateur (Sprint 2).
