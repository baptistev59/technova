<?php

namespace App\Controller\Api;

use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

#[Route('/api/products')]
class ProductApiController extends AbstractController
{
    public function __construct(private ProductRepository $productRepository)
    {
    }

    #[Route('', name: 'api_products_index', methods: ['GET'])]
    public function index(): JsonResponse
    {
        $products = $this->productRepository->filterBy([]);

        return $this->json(array_map([$this, 'serializeProduct'], $products));
    }

    #[Route('/{slug}', name: 'api_products_show', methods: ['GET'])]
    public function show(string $slug): JsonResponse
    {
        $product = $this->productRepository->findOneBy(['slug' => $slug, 'isPublished' => true]);

        if (!$product) {
            throw new NotFoundHttpException('Produit introuvable');
        }

        return $this->json($this->serializeProduct($product, true));
    }

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
