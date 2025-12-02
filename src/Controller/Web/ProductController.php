<?php

namespace App\Controller\Web;

use App\Entity\Product;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Route de fiche produit. Symfony injecte directement l'entité via le slug.
 */
class ProductController extends AbstractController
{
    #[Route('/produit/{slug}', name: 'product_show')]
    public function show(#[MapEntity(mapping: ['slug' => 'slug'])] Product $product): Response
    {
        $options = [
            'colors' => $this->guessColors($product),
            'variants' => $this->guessVariants($product),
        ];

        $specifications = $this->buildSpecifications($product);

        return $this->render('catalog/product_show.html.twig', [
            'product' => $product,
            'options' => $options,
            'specifications' => $specifications,
        ]);
    }

    /**
     * @return list<string>
     */
    private function guessColors(Product $product): array
    {
        return match ($product->getCategory()?->getSlug()) {
            'smart-mobility' => ['Noir carbone', 'Bleu cobalt', 'Vert néon'],
            'immersive-vr' => ['Blanc arctique', 'Noir profond'],
            'bio-wearables' => ['Argent', 'Or rose', 'Titane'],
            default => ['Noir', 'Bleu TechNova', 'Gris acier'],
        };
    }

    /**
     * @return list<string>
     */
    private function guessVariants(Product $product): array
    {
        return match ($product->getType()) {
            'computer' => ['16 Go / 512 Go', '32 Go / 1 To'],
            'mobility' => ['Batterie 80 km', 'Batterie 120 km'],
            'wearable' => ['Taille S', 'Taille M', 'Taille L'],
            default => ['Edition Standard', 'Edition Pro'],
        };
    }

    /**
     * @return array<string, string>
     */
    private function buildSpecifications(Product $product): array
    {
        return [
            'Type' => ucfirst((string) $product->getType()),
            'Catégorie' => $product->getCategory()?->getName() ?? 'N/A',
            'Marque' => $product->getBrand()?->getName() ?? 'TechNova',
            'Boutique' => $product->getShop()?->getName() ?? 'Marketplace',
            'SKU' => $product->getSku() ?? 'N/A',
            'Code-barres' => $product->getBarcode() ?? 'N/A',
        ];
    }
}
