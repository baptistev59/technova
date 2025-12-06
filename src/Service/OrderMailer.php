<?php

namespace App\Service;

use App\Entity\CustomerOrder;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Part\DataPart;

class OrderMailer
{
    public function __construct(
        private readonly MailerInterface $mailer,
        private readonly ParameterBagInterface $params,
        private readonly ?string $mailerFrom = null
    ) {
    }

    public function sendConfirmation(CustomerOrder $order): void
    {
        $owner = $order->getOwner();
        $email = $owner?->getEmail();

        if (!$owner || !$email) {
            return;
        }

        $fromAddress = $this->mailerFrom
            ? Address::create($this->mailerFrom)
            : new Address('no-reply@technova.local', 'TechNova');

        $baseUrl = rtrim(
            $this->params->has('router.default_uri') ? (string) $this->params->get('router.default_uri') : ($_ENV['DEFAULT_URI'] ?? 'https://technova.local'),
            '/'
        );

        $publicDir = rtrim((string) $this->params->get('kernel.project_dir'), '/') . '/public';

        $items = [];
        foreach ($order->getItems() as $item) {
            $image = $item->getProductImage();
            $imageUrl = null;
            $embedPath = null;

            if ($image) {
                if (str_starts_with($image, 'http')) {
                    $imageUrl = $image;
                } else {
                    $relative = ltrim(parse_url($image, PHP_URL_PATH) ?? $image, '/');
                    $fullPath = $publicDir . '/' . $relative;

                    if (is_file($fullPath)) {
                        $embedPath = $fullPath;
                    }

                    $imageUrl = sprintf('%s/%s', $baseUrl, $relative);
                }
            }

            $items[] = [
                'name' => $item->getProductName(),
                'quantity' => $item->getQuantity(),
                'unitPrice' => $item->getUnitPrice(),
                'lineTotal' => $item->getLineTotal(),
                'imageCid' => null,
                'imageUrl' => $imageUrl,
                '_embedPath' => $embedPath,
            ];
        }

        $emailMessage = (new TemplatedEmail())
            ->from($fromAddress)
            ->to($email)
            ->subject(sprintf('TechNova — Confirmation de commande %s', $order->getReference()))
            ->htmlTemplate('emails/order_confirmation.html.twig')
            ->textTemplate('emails/order_confirmation.text.twig');

        foreach ($items as $index => $itemData) {
            if (!empty($itemData['_embedPath'])) {
                $inlinePart = DataPart::fromPath($itemData['_embedPath'])->asInline();
                $emailMessage->addPart($inlinePart);
                $items[$index]['imageCid'] = 'cid:' . $inlinePart->getContentId();
            }

            unset($items[$index]['_embedPath']);
        }

        $emailMessage->context([
            'order' => $order,
            'items' => $items,
            'address' => $order->getShippingAddress(),
            'total' => $order->getTotalAmount(),
            'baseUrl' => $baseUrl,
        ]);

        $this->mailer->send($emailMessage);
    }
}
