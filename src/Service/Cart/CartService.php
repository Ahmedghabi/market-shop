<?php

namespace App\Service\Cart;

use App\Dto\Cart\CartCheckoutInput;
use App\Dto\Cart\CartCheckoutOutput;
use App\Dto\Cart\CartItemOutput;
use App\Dto\Cart\CartOutput;
use App\Entity\Boutique;
use App\Entity\Cart;
use App\Entity\CartItem;
use App\Entity\Customer;
use App\Entity\Order;
use App\Entity\PaymentMethod;
use App\Enum\OrderChannel;
use App\Enum\OrderStatus;
use App\Repository\PaymentMethodRepository;
use App\Repository\ShopPaymentMethodRepository;
use App\Repository\BoutiqueRepository;
use App\Repository\CartItemRepository;
use App\Repository\CartRepository;
use App\Repository\CustomerRepository;
use App\Repository\ProductRepository;
use App\Service\AppConfigService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Uid\Uuid;

final readonly class CartService
{
    public function __construct(
        private BoutiqueRepository $boutiques,
        private ProductRepository $products,
        private CartRepository $carts,
        private CartItemRepository $cartItems,
        private CustomerRepository $customers,
        private PaymentMethodRepository $paymentMethods,
        private ShopPaymentMethodRepository $shopPaymentMethods,
        private EntityManagerInterface $em,
        private RequestStack $requestStack,
        private AppConfigService $appConfig,
    ) {
    }

    public function currentCart(string $boutiqueId): CartOutput
    {
        return $this->toOutput($this->getOrCreateCart($boutiqueId));
    }

    public function addItem(string $boutiqueId, string $productId, int $quantity): CartOutput
    {
        if ($quantity < 1) {
            throw new BadRequestHttpException('Quantity must be greater than zero.');
        }

        $cart = $this->getOrCreateCart($boutiqueId);
        $product = $this->products->find($productId);
        if (!$product || (string) $product->getBoutique()->getId() !== (string) $cart->getBoutique()->getId() || !$product->isActive()) {
            throw new NotFoundHttpException('Product not found.');
        }

        $cart->addItem($product, $quantity);
        $this->em->flush();

        return $this->toOutput($cart);
    }

    public function updateItem(string $boutiqueId, string $itemId, int $quantity): CartOutput
    {
        if ($quantity < 1) {
            throw new BadRequestHttpException('Quantity must be greater than zero.');
        }

        $cart = $this->getOrCreateCart($boutiqueId);
        $item = $this->findCartItem($cart, $itemId);
        $item->changeQuantity($quantity);
        $cart->touch();
        $this->em->flush();

        return $this->toOutput($cart);
    }

    public function removeItem(string $boutiqueId, string $itemId): CartOutput
    {
        $cart = $this->getOrCreateCart($boutiqueId);
        $item = $this->findCartItem($cart, $itemId);
        $cart->removeItem($item);
        $this->em->remove($item);
        $this->em->flush();

        return $this->toOutput($cart);
    }

    public function checkout(string $boutiqueId, CartCheckoutInput $input): CartCheckoutOutput
    {
        $cart = $this->getOrCreateCart($boutiqueId);
        if ($cart->getItems()->isEmpty()) {
            throw new BadRequestHttpException('Cart is empty.');
        }

        $customer = $this->resolveCustomer($cart->getBoutique(), $input);
        $cart->setCustomer($customer);
        $paymentMethod = $this->resolvePaymentMethod($cart->getBoutique(), $input->paymentMethodCode, $cart->getTotalCents());

        $order = new Order(
            $cart->getBoutique(),
            $customer,
            OrderChannel::Online,
            OrderStatus::Pending,
            $cart->getTotalCents(),
            0,
            $cart->getTotalCents(),
            $cart->getCurrency(),
        );
        $order->setCustomerSnapshot(
            $this->customerName($input),
            strtolower(trim((string) $input->customerEmail)) ?: null,
            $this->nullableTrim($input->phone),
            $this->nullableTrim($input->shippingAddress),
            $this->nullableTrim($input->shippingCity),
        );
        $order->setPaymentMethodCode($paymentMethod?->getCode());

        foreach ($cart->getItems() as $item) {
            $product = $item->getProduct();
            $order->addItem(
                $product,
                $product?->getName() ?? 'Produit supprimé',
                $product?->getSku() ?? '',
                $item->getQuantity(),
                $item->getUnitPriceCents(),
            );
        }

        $cart->markOrdered();
        $this->em->persist($order);
        $this->em->flush();
        $this->clearCookieFor($cart->getBoutique());

        $output = new CartCheckoutOutput();
        $output->orderId = (string) $order->getId();
        $output->cartId = (string) $cart->getId();
        $output->status = OrderStatus::Pending->value;
        $output->paymentStatus = $order->getPaymentStatus()->value;
        $output->paymentMethodCode = $order->getPaymentMethodCode();
        $output->totalCents = $order->getTotalCents();
        $output->currency = $order->getCurrency();

        return $output;
    }

    private function getOrCreateCart(string $boutiqueId): Cart
    {
        $boutique = $this->findBoutique($boutiqueId);
        $request = $this->requestStack->getCurrentRequest();
        $cookieName = $this->cookieName($boutique);
        $cookieCartId = $request?->cookies->get($cookieName);

        if (is_string($cookieCartId) && Uuid::isValid($cookieCartId)) {
            $cart = $this->carts->findActiveForBoutique($cookieCartId, $boutique);
            if ($cart instanceof Cart) {
                $this->writeCookieFor($cart);

                return $cart;
            }
        }

        $cart = new Cart($boutique, bin2hex(random_bytes(32)), null, currency: 'EUR');
        $this->em->persist($cart);
        $this->em->flush();
        $this->writeCookieFor($cart);

        return $cart;
    }

    private function findBoutique(string $boutiqueId): Boutique
    {
        $boutique = $this->boutiques->find($boutiqueId);
        if (!$boutique instanceof Boutique) {
            throw new NotFoundHttpException('Boutique not found.');
        }

        return $boutique;
    }

    private function findCartItem(Cart $cart, string $itemId): CartItem
    {
        $item = $this->cartItems->find($itemId);
        if (!$item instanceof CartItem || (string) $item->getCart()->getId() !== (string) $cart->getId()) {
            throw new NotFoundHttpException('Cart item not found.');
        }

        return $item;
    }

    private function resolveCustomer(Boutique $boutique, CartCheckoutInput $input): ?Customer
    {
        $email = strtolower(trim((string) $input->customerEmail));
        if ('' === $email) {
            return null;
        }

        $customer = $this->customers->findOneBy(['boutique' => $boutique, 'email' => $email]);
        if ($customer instanceof Customer) {
            return $customer;
        }

        $customer = new Customer(
            $boutique,
            $email,
            $this->nullableTrim($input->firstName),
            $this->nullableTrim($input->lastName),
            $this->nullableTrim($input->phone),
        );
        $this->em->persist($customer);

        return $customer;
    }

    private function resolvePaymentMethod(Boutique $boutique, ?string $paymentMethodCode, int $cartTotalCents): ?PaymentMethod
    {
        $activeMethods = $this->shopPaymentMethods->findActiveForBoutique($boutique);
        if ([] === $activeMethods) {
            return null;
        }

        if (!$this->appConfig->isModuleEnabled('paiements')) {
            throw new BadRequestHttpException('Payments are disabled globally.');
        }

        $paymentsConfig = $this->appConfig->section('payments');

        $paymentMethodCode = strtoupper(trim((string) $paymentMethodCode));
        if ('' === $paymentMethodCode) {
            throw new BadRequestHttpException('Payment method is required.');
        }

        $paymentMethod = $this->paymentMethods->findOneByCode($paymentMethodCode);
        if (!$paymentMethod instanceof PaymentMethod || !$this->shopPaymentMethods->hasActiveCodeForBoutique($boutique, $paymentMethodCode)) {
            throw new BadRequestHttpException('Payment method is not available for this boutique.');
        }

        $visibleMethods = is_array($paymentsConfig['visible_methods'] ?? null) ? array_map('strtoupper', array_map('strval', $paymentsConfig['visible_methods'])) : [];
        if ([] !== $visibleMethods && !in_array($paymentMethodCode, $visibleMethods, true)) {
            throw new BadRequestHttpException('Payment method is disabled globally.');
        }

        if ('CASH_ON_DELIVERY' === $paymentMethodCode && !($paymentsConfig['cash_on_delivery_enabled'] ?? true)) {
            throw new BadRequestHttpException('Cash on delivery is disabled globally.');
        }
        if ('BANK_TRANSFER' === $paymentMethodCode && !($paymentsConfig['bank_transfer_enabled'] ?? true)) {
            throw new BadRequestHttpException('Bank transfer is disabled globally.');
        }
        if (!in_array($paymentMethodCode, ['CASH_ON_DELIVERY', 'BANK_TRANSFER'], true) && !($paymentsConfig['online_payment_enabled'] ?? true)) {
            throw new BadRequestHttpException('Online payments are disabled globally.');
        }

        $shopMethod = null;
        foreach ($activeMethods as $candidate) {
            if ($candidate->getPaymentMethod()->getCode() === $paymentMethodCode) {
                $shopMethod = $candidate;
                break;
            }
        }
        if (null === $shopMethod) {
            throw new BadRequestHttpException('Payment method is not available for this boutique.');
        }
        if (null !== $shopMethod->getMinimumAmountCents() && $cartTotalCents < $shopMethod->getMinimumAmountCents()) {
            throw new BadRequestHttpException('Cart total is below the minimum amount for this payment method.');
        }
        if (null !== $shopMethod->getMaximumAmountCents() && $cartTotalCents > $shopMethod->getMaximumAmountCents()) {
            throw new BadRequestHttpException('Cart total exceeds the maximum amount for this payment method.');
        }

        return $paymentMethod;
    }

    private function nullableTrim(?string $value): ?string
    {
        $value = trim((string) $value);

        return '' === $value ? null : $value;
    }

    private function customerName(CartCheckoutInput $input): ?string
    {
        $name = trim(sprintf('%s %s', (string) $input->firstName, (string) $input->lastName));

        return '' === $name ? null : $name;
    }

    private function writeCookieFor(Cart $cart): void
    {
        $request = $this->requestStack->getCurrentRequest();
        if (null === $request) {
            return;
        }

        $request->attributes->set(CartCookieSubscriber::COOKIE_NAME_ATTRIBUTE, $this->cookieName($cart->getBoutique()));
        $request->attributes->set(CartCookieSubscriber::COOKIE_VALUE_ATTRIBUTE, (string) $cart->getId());
    }

    private function clearCookieFor(Boutique $boutique): void
    {
        $request = $this->requestStack->getCurrentRequest();
        if (null === $request) {
            return;
        }

        $request->attributes->set(CartCookieSubscriber::COOKIE_NAME_ATTRIBUTE, $this->cookieName($boutique));
        $request->attributes->set(CartCookieSubscriber::COOKIE_CLEAR_ATTRIBUTE, true);
    }

    private function cookieName(Boutique $boutique): string
    {
        return sprintf('market_shop_cart_%s', $boutique->getSlug());
    }

    public function toOutput(Cart $cart): CartOutput
    {
        $output = new CartOutput();
        $output->id = (string) $cart->getId();
        $output->boutiqueId = (string) $cart->getBoutique()->getId();
        $output->status = $cart->getStatus()->value;
        $output->currency = $cart->getCurrency();
        $output->itemsCount = $cart->getItems()->count();
        $output->totalCents = $cart->getTotalCents();
        $output->items = array_map([$this, 'toItemOutput'], $cart->getItems()->toArray());
        $output->createdAt = $cart->getCreatedAt();
        $output->updatedAt = $cart->getUpdatedAt();
        $output->expiresAt = $cart->getExpiresAt();

        return $output;
    }

    private function toItemOutput(CartItem $item): CartItemOutput
    {
        $product = $item->getProduct();
        $output = new CartItemOutput();
        $output->id = (string) $item->getId();
        $output->productId = $product ? (string) $product->getId() : null;
        $output->productName = $product?->getName();
        $output->sku = $product?->getSku();
        $output->quantity = $item->getQuantity();
        $output->unitPriceCents = $item->getUnitPriceCents();
        $output->totalCents = $item->getTotalCents();

        return $output;
    }
}
