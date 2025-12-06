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
            ->setParameter('published', true)
            ->setParameter('featured', true)
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
        $hasSearchOrdering = false;

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
            $normalized = mb_strtolower(trim(preg_replace('/\s+/', ' ', (string) $filters['search'])));
            if ($normalized !== '') {
                $terms = array_values(array_filter(explode(' ', $normalized)));
                if ($terms !== []) {
                    $orExpressions = [];
                    $scoreParts = [];

                    foreach ($terms as $index => $term) {
                        $param = 'term_' . $index;
                        $condition = sprintf(
                            '(LOWER(p.name) LIKE :%1$s OR LOWER(p.shortDescription) LIKE :%1$s)',
                            $param
                        );
                        $orExpressions[] = $condition;
                        $scoreParts[] = sprintf('CASE WHEN %s THEN 1 ELSE 0 END', $condition);
                        $qb->setParameter($param, '%' . $term . '%');
                    }

                    $qb->andWhere(implode(' OR ', $orExpressions));
                    $qb->addSelect('(' . implode(' + ', $scoreParts) . ') AS HIDDEN relevance');
                    $qb->addOrderBy('relevance', 'DESC');
                    $hasSearchOrdering = true;
                }
            }
        }

        $sort = $filters['sort'] ?? 'newest';
        $orderMethod = $hasSearchOrdering ? 'addOrderBy' : 'orderBy';
        match ($sort) {
            'price_asc' => $qb->{$orderMethod}('p.price', 'ASC'),
            'price_desc' => $qb->{$orderMethod}('p.price', 'DESC'),
            'oldest' => $qb->{$orderMethod}('p.createdAt', 'ASC'),
            default => $qb->{$orderMethod}('p.createdAt', 'DESC'),
        };

        return $qb->getQuery()->getResult();
    }
}
