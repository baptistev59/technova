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
        $categorySlug = $request->query->get('category');
        $brandSlug = $request->query->get('brand');

        // On centralise les paramètres pour pouvoir les réafficher dans la vue
        $filters = [
            'category' => $categorySlug,
            'brand' => $brandSlug,
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
        ]);
    }
}
