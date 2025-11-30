<?php

namespace App\Controller\Api;

use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * API publique qui expose la liste de produits prêts pour le front (React/Twig).
 * Chaque méthode renvoie un JSON épuré pour ne pas exposer d'informations inutiles.
 */
#[Route('/api/products')]
class ProductApiController extends AbstractController
{
    public function __construct(private ProductRepository $productRepository)
    {
    }

    #[Route('', name: 'api_products_index', methods: ['GET'])]
    public function index(): JsonResponse
    {
        // Pas de filtre → on récupère les produits publiés via le repo dédié
        $products = $this->productRepository->filterBy([]);

        return $this->json(array_map([$this, 'serializeProduct'], $products));
    }

    #[Route('/{slug}', name: 'api_products_show', methods: ['GET'])]
    public function show(string $slug): JsonResponse
    {
        // Rappel : on force isPublished pour éviter d'exposer un brouillon
        $product = $this->productRepository->findOneBy(['slug' => $slug, 'isPublished' => true]);

        if (!$product) {
            throw new NotFoundHttpException('Produit introuvable');
        }

        return $this->json($this->serializeProduct($product, true));
    }

    /**
     * Mini normalizer maison : idéal pour garder le contrôle sur les champs exposés.
     */
    private function serializeProduct($product, bool $includeDetails = false): array
    {
        $data = [
            'name' => $product->getName(),
            'slug' => $product->getSlug(),
            'shortDescription' => $product->getShortDescription(),
            'price' => (float) $product->getPrice(),
            'brand' => $product->getBrand()?->getName(),
            'category' => $product->getCategory()->getName(),
            'thumbnail' => $product->getImages()->first()?->getUrl(),
        ];

        if ($includeDetails) {
            // Mode fiche complète : on expose description, stock, galeries et avis
            $data['description'] = $product->getDescription();
            $data['stock'] = $product->getStock();
            $data['images'] = array_map(fn($img) => $img->getUrl(), $product->getImages()->toArray());
            $data['reviews'] = array_map(fn($review) => [
                'rating' => $review->getRating(),
                'comment' => $review->getComment(),
            ], $product->getReviews()->toArray());
        }

        return $data;
    }
}
