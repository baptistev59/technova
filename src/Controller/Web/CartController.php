<?php

namespace App\Controller\Web;

use App\Entity\Product;
use App\Security\ViewerAccessChecker;
use App\Service\CartService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Gestion du panier côté interface Twig.
 */
#[Route('/panier')]
class CartController extends AbstractController
{
    public function __construct(
        private readonly CartService $cartService,
        private readonly ViewerAccessChecker $viewerAccessChecker
    ) {
    }

    #[Route('', name: 'app_cart_show', methods: ['GET'])]
    public function show(Request $request): Response
    {
        if ($response = $this->viewerAccessChecker->requireViewer($this->getUser(), $request->getSession())) {
            return $response;
        }
        $summary = $this->cartService->getSummary();

        return $this->render('cart/show.html.twig', [
            'cart' => $summary,
        ]);
    }

    #[Route('/ajouter/{id}', name: 'app_cart_add', methods: ['POST'])]
    public function add(Product $product, Request $request): Response
    {
        if ($response = $this->viewerAccessChecker->requireViewer($this->getUser(), $request->getSession())) {
            return $response;
        }
        if (!$this->isCsrfTokenValid('add_to_cart_'.$product->getId(), (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Jeton CSRF invalide.');
        }

        $quantity = max(1, (int) $request->request->get('quantity', 1));
        $this->cartService->addProduct($product, $quantity);
        if ('product' !== $request->request->get('redirect_to')) {
            $this->addFlash('success', sprintf('%s a été ajouté au panier.', $product->getName()));
        }

        $redirectTo = $request->request->get('redirect_to');
        if ('product' === $redirectTo && $request->request->get('product_slug')) {
            $slug = (string) $request->request->get('product_slug');
            return $this->redirectToRoute('product_show', [
                'slug' => $slug,
                'cart_added' => 1,
            ]);
        }

        return $this->redirectToRoute('app_cart_show');
    }

    #[Route('/modifier/{id}', name: 'app_cart_update', methods: ['POST'])]
    public function update(Product $product, Request $request): Response
    {
        if ($response = $this->viewerAccessChecker->requireViewer($this->getUser(), $request->getSession())) {
            return $response;
        }
        if (!$this->isCsrfTokenValid('update_cart_'.$product->getId(), (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Jeton CSRF invalide.');
        }

        $quantity = max(0, (int) $request->request->get('quantity', 1));
        $this->cartService->setProductQuantity($product, $quantity);
        $summary = $this->cartService->getSummary();

        if ($request->isXmlHttpRequest()) {
            $currentLine = null;
            foreach ($summary['items'] as $item) {
                if ($item['product']->getId() === $product->getId()) {
                    $currentLine = [
                        'productId' => $product->getId(),
                        'quantity' => $item['quantity'],
                        'lineTotal' => $item['lineTotal'],
                    ];
                }
            }

            return $this->json([
                'line' => $currentLine,
                'summary' => [
                    'total' => $summary['total'],
                    'totalQuantity' => $summary['totalQuantity'],
                ],
            ]);
        }

        return $this->redirectToRoute('app_cart_show');
    }

    #[Route('/supprimer/{id}', name: 'app_cart_remove', methods: ['POST'])]
    public function remove(Product $product, Request $request): Response
    {
        if ($response = $this->viewerAccessChecker->requireViewer($this->getUser(), $request->getSession())) {
            return $response;
        }
        if (!$this->isCsrfTokenValid('remove_from_cart_'.$product->getId(), (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Jeton CSRF invalide.');
        }

        $this->cartService->removeProduct($product);
        $this->addFlash('success', sprintf('%s a été retiré du panier.', $product->getName()));

        return $this->redirectToRoute('app_cart_show');
    }

    #[Route('/vider', name: 'app_cart_clear', methods: ['POST'])]
    public function clear(Request $request): Response
    {
        if ($response = $this->viewerAccessChecker->requireViewer($this->getUser(), $request->getSession())) {
            return $response;
        }
        if (!$this->isCsrfTokenValid('cart_clear', (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Jeton CSRF invalide.');
        }

        $this->cartService->clear();
        $this->addFlash('success', 'Panier vidé.');

        return $this->redirectToRoute('app_cart_show');
    }
}
