<?php

namespace App\Repository;

use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Product>
 */
class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    /**
     * Retourne les derniers produits publiés pour alimenter la home/sections "nouveautés".
     *
     * @return Product[]
     */
    public function findLatestPublished(int $limit = 8): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.isPublished = :published')
            ->setParameter('published', true)
            ->orderBy('p.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Sélectionne les produits mis à la une.
     *
     * @return Product[]
     */
    public function findFeaturedPublished(int $limit = 3): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.isPublished = :published')
            ->andWhere('p.isFeatured = :featured')
            ->setParameters([
                'published' => true,
                'featured' => true,
            ])
            ->orderBy('p.updatedAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Filtrage partagé entre l'API et les pages Twig (catégorie + marque).
     *
     * @param array{
     *     category?: string|null,
     *     brand?: string|null,
     *     minPrice?: float|null,
     *     maxPrice?: float|null,
     *     search?: string|null,
     *     sort?: string|null
     * } $filters
     * @return Product[]
     */
    public function filterBy(array $filters): array
    {
        $qb = $this->createQueryBuilder('p')
            ->leftJoin('p.category', 'c')
            ->addSelect('c')
            ->leftJoin('p.brand', 'b')
            ->addSelect('b')
            ->andWhere('p.isPublished = :published')
            ->setParameter('published', true)
            ->orderBy('p.createdAt', 'DESC');

        if (!empty($filters['category'])) {
            $qb->andWhere('c.slug = :category')
                ->setParameter('category', $filters['category']);
        }

        if (!empty($filters['brand'])) {
            $qb->andWhere('b.slug = :brand')
                ->setParameter('brand', $filters['brand']);
        }

        if (isset($filters['minPrice']) && is_numeric($filters['minPrice'])) {
            $qb->andWhere('p.price >= :minPrice')
                ->setParameter('minPrice', (float) $filters['minPrice']);
        }

        if (isset($filters['maxPrice']) && is_numeric($filters['maxPrice'])) {
            $qb->andWhere('p.price <= :maxPrice')
                ->setParameter('maxPrice', (float) $filters['maxPrice']);
        }

        if (!empty($filters['search'])) {
            $qb->andWhere('p.name LIKE :term OR p.shortDescription LIKE :term')
                ->setParameter('term', '%' . $filters['search'] . '%');
        }

        $sort = $filters['sort'] ?? 'newest';
        match ($sort) {
            'price_asc' => $qb->orderBy('p.price', 'ASC'),
            'price_desc' => $qb->orderBy('p.price', 'DESC'),
            'oldest' => $qb->orderBy('p.createdAt', 'ASC'),
            default => $qb->orderBy('p.createdAt', 'DESC'),
        };

        return $qb->getQuery()->getResult();
    }
}
