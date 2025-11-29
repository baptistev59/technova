<?php

namespace App\Controller\Web;

use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomepageController extends AbstractController
{
    #[Route('/', name: 'homepage')]
    public function __invoke(ProductRepository $productRepository): Response
    {
        $latestProducts = $productRepository->findLatestPublished(6);

        return $this->render('catalog/homepage.html.twig', [
            'products' => $latestProducts,
        ]);
    }
}
