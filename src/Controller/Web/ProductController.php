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
        return $this->render('catalog/product_show.html.twig', [
            'product' => $product,
        ]);
    }
}
