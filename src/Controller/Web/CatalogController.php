<?php

namespace App\Controller\Web;

use App\Repository\BrandRepository;
use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Liste des produits avec filtres simples (catégorie + marque) côté Twig.
 */
class CatalogController extends AbstractController
{
    #[Route('/catalogue', name: 'catalog_index')]
    public function index(
        Request $request,
        ProductRepository $productRepository,
        CategoryRepository $categoryRepository,
        BrandRepository $brandRepository
    ): Response {
        $filters = [
            'category' => $request->query->get('category'),
            'brand' => $request->query->get('brand'),
            'minPrice' => $request->query->get('minPrice'),
            'maxPrice' => $request->query->get('maxPrice'),
            'search' => $request->query->get('search'),
            'sort' => $request->query->get('sort'),
        ];

        // Le repository connaît déjà la logique de filtres → réutilisation côté API/Twig
        $products = $productRepository->filterBy($filters);
        $categories = $categoryRepository->findAll();
        $brands = $brandRepository->findAll();

        return $this->render('catalog/index.html.twig', [
            'products' => $products,
            'categories' => $categories,
            'brands' => $brands,
            'activeFilters' => $filters,
            'resultsCount' => count($products),
        ]);
    }
}
