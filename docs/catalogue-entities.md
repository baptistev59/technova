# Catalogue Entities Summary

- Category: hierarchical, unique slug, linked to products
- Brand: unique slug, optional logo, linked to products
- Shop: belongs to a Vendor, exposes products, slug unique
- Product: belongs to Category/Brand/Shop, has pricing info, featured flag, published flag
- ProductImage: child of Product, order/position/isMain, metadata
- ProductReview: rating/comment authored by User for Product

Repositories created for each entity, with ProductRepository featuring `findLatestPublished()` and `filterBy()`.

Fixtures (`php bin/console doctrine:fixtures:load --purge-with-truncate`) inject:
- Admin `admin@test.fr` (password `123456`)
- Un vendor + boutique
- Catégories : Informatique / Smartphones & Tablettes / Maison connectée
- Marques : NovaTech / UrbanGears / HomeSense
- Produits de démo avec images et avis

Pages Twig : `/`, `/catalogue`, `/produit/{slug}`. API : `/api/products`, `/api/products/{slug}`.
Next steps: styliser et brancher les endpoints panier/utilisateur (Sprint 2).
