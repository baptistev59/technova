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
     * Filtrage partagé entre l'API et les pages Twig (catégorie + marque).
     *
     * @param array{category?: string|null, brand?: string|null} $filters
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

        return $qb->getQuery()->getResult();
    }
}
