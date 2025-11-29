# Catalogue Entities Summary

- Category: hierarchical, unique slug, linked to products
- Brand: unique slug, optional logo, linked to products
- Shop: belongs to a Vendor, exposes products, slug unique
- Product: belongs to Category/Brand/Shop, has pricing info, featured flag, published flag
- ProductImage: child of Product, order/position/isMain, metadata
- ProductReview: rating/comment authored by User for Product

Repositories created for each entity, with ProductRepository featuring `findLatestPublished()`.

Next steps:
1. Run migrations (`php bin/console make:migration` & `doctrine:migrations:migrate`).
2. Add fixtures for categories, brands, shops, products.
3. Implement Twig views (`/`, `/catalogue`, `/produit/{slug}`) and API counterparts.
4. Test endpoints via Postman.
