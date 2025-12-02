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
        $optionGroups = [];
        foreach ($product->getAttributes() as $attribute) {
            $values = [];
            foreach ($attribute->getValues() as $value) {
                $values[] = [
                    'slug' => $value->getSlug(),
                    'label' => $value->getValue(),
                    'color' => $value->getColorHex(),
                ];
            }

            $optionGroups[] = [
                'slug' => $attribute->getSlug(),
                'name' => $attribute->getName(),
                'type' => $attribute->getInputType(),
                'values' => $values,
            ];
        }

        $variantData = [];
        foreach ($product->getVariants() as $variant) {
            $variantData[] = [
                'id' => $variant->getId(),
                'price' => $variant->getPrice(),
                'promoPrice' => $variant->getPromoPrice(),
                'stock' => $variant->getStock(),
                'isAvailable' => $variant->isAvailable(),
                'configuration' => $variant->getConfiguration(),
                'metadata' => $variant->getMetadata(),
            ];
        }

        $specifications = $this->buildSpecifications($product);

        return $this->render('catalog/product_show.html.twig', [
            'product' => $product,
            'optionGroups' => $optionGroups,
            'variantData' => $variantData,
            'specifications' => $specifications,
        ]);
    }

    /**
     * @return array<string, string>
     */
    private function buildSpecifications(Product $product): array
    {
        return [
            'Type' => ucfirst((string) ($product->getType() ?? 'standard')),
            'Catégorie' => $product->getCategory()?->getName() ?? 'N/A',
            'Marque' => $product->getBrand()?->getName() ?? 'TechNova',
            'Boutique' => $product->getShop()?->getName() ?? 'Marketplace',
            'SKU' => $product->getSku() ?? 'N/A',
            'Code-barres' => $product->getBarcode() ?? 'N/A',
        ];
    }
}
