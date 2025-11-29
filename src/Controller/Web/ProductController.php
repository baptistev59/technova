<?php

namespace App\Controller\Web;

use App\Entity\Product;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class ProductController extends AbstractController
{
    #[Route('/produit/{slug}', name: 'product_show')]
    public function show(Product $product): Response
    {
        return $this->render('catalog/product_show.html.twig', [
            'product' => $product,
        ]);
    }
}
