<?php

namespace App\Service;

use App\Entity\Product;
use App\Entity\SavedCart;
use App\Entity\User;
use App\Repository\ProductRepository;
use App\Repository\SavedCartRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * Stocke le panier du visiteur dans la session et calcule les totaux.
 */
class CartService
{
    private const SESSION_KEY = 'cart.items';

    private bool $cartHydrated = false;

    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly ProductRepository $productRepository,
        private readonly UserRepository $userRepository,
        private readonly SavedCartRepository $savedCartRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly Security $security
    ) {
    }

    public function addProduct(Product $product, int $quantity = 1): void
    {
        $quantity = max(1, $quantity);
        $cart = $this->getCart();
        $productId = (string) $product->getId();
        $current = $cart[$productId] ?? 0;
        $this->setProductQuantity($product, $current + $quantity);
    }

    public function setProductQuantity(Product $product, int $quantity): void
    {
        $quantity = max(0, $quantity);
        $cart = $this->getCart();
        $productId = (string) $product->getId();

        if ($quantity === 0) {
            unset($cart[$productId]);
        } else {
            $cart[$productId] = min($product->getStock(), $quantity);
        }

        $this->saveCart($cart);
    }

    public function removeProduct(Product $product): void
    {
        $this->setProductQuantity($product, 0);
    }

    public function clear(): void
    {
        $this->saveCart([]);
    }

    /**
     * @return array{items: list<array{product: Product, quantity: int, lineTotal: float, unitPrice: float, stock: int}>, total: float, totalQuantity: int}
     */
    public function getSummary(): array
    {
        $cart = $this->getCart();
        if ($cart === []) {
            return ['items' => [], 'total' => 0.0, 'totalQuantity' => 0];
        }

        $productIds = array_map('intval', array_keys($cart));
        $products = $this->productRepository->findBy(['id' => $productIds]);

        $items = [];
        $totalAmount = 0.0;
        $totalQuantity = 0;

        foreach ($products as $product) {
            $quantity = $cart[(string) $product->getId()] ?? 0;
            if ($quantity <= 0) {
                continue;
            }

            $lineTotal = $quantity * $product->getPrice();
            $items[] = [
                'product' => $product,
                'quantity' => $quantity,
                'lineTotal' => $lineTotal,
                'unitPrice' => $product->getPrice(),
                'stock' => $product->getStock(),
            ];
            $totalAmount += $lineTotal;
            $totalQuantity += $quantity;
        }

        return [
            'items' => $items,
            'total' => $totalAmount,
            'totalQuantity' => $totalQuantity,
        ];
    }

    private function getCart(): array
    {
        $this->ensureCartLoadedFromSnapshot();

        return $this->getSession()->get(self::SESSION_KEY, []);
    }

    private function saveCart(array $cart): void
    {
        $this->getSession()->set(self::SESSION_KEY, $cart);
        $this->syncSnapshot($cart);
    }

    private function ensureCartLoadedFromSnapshot(): void
    {
        if ($this->cartHydrated) {
            return;
        }
        $this->cartHydrated = true;

        $session = $this->getSession();
        $current = $session->get(self::SESSION_KEY, null);
        if (is_array($current) && $current !== []) {
            return;
        }

        $user = $this->resolveViewerUser();
        if (!$user) {
            return;
        }

        $snapshot = $this->savedCartRepository->findOneBy(['owner' => $user]);
        if ($snapshot instanceof SavedCart) {
            $session->set(self::SESSION_KEY, $snapshot->getItems());
        }
    }

    private function syncSnapshot(array $cart): void
    {
        $user = $this->resolveViewerUser();
        if (!$user) {
            return;
        }

        $snapshot = $this->savedCartRepository->findOneBy(['owner' => $user]);

        if ($cart === []) {
            if ($snapshot) {
                $this->entityManager->remove($snapshot);
                $this->entityManager->flush();
            }

            return;
        }

        if (!$snapshot) {
            $snapshot = (new SavedCart())->setOwner($user);
        }

        $snapshot->setItems($cart);
        $snapshot->setUpdatedAt(new \DateTimeImmutable());

        $this->entityManager->persist($snapshot);
        $this->entityManager->flush();
    }

    private function resolveViewerUser(): ?User
    {
        $user = $this->security->getUser();
        if ($user instanceof User) {
            return $user;
        }

        $session = $this->requestStack->getSession();
        if ($session && $session->has('recent_user_id')) {
            $userId = (int) $session->get('recent_user_id');
            if ($userId > 0) {
                return $this->userRepository->find($userId);
            }
        }

        return null;
    }

    private function getSession(): SessionInterface
    {
        $session = $this->requestStack->getSession();
        if (!$session->isStarted()) {
            $session->start();
        }

        return $session;
    }
}
