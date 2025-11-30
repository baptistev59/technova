<?php

namespace App\EventSubscriber;

use App\Service\AuditLoggerService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationFailureEvent;
use App\Entity\User;

/**
 * Observe les succès/échecs de connexion pour alimenter la table audit_log.
 */
class LoginAuditSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private AuditLoggerService $audit
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            LoginSuccessEvent::class => 'onLoginSuccess',
            AuthenticationFailureEvent::class => 'onLoginFailure',
        ];
    }

    public function onLoginSuccess(LoginSuccessEvent $event): void
    {
        /** @var User|null $user */
        $user = $event->getUser();
        $resourceId = null;
        $email = null;

        if ($user instanceof User) {
            $resourceId = $user->getId();
            $email = $user->getUserIdentifier();
        }

        // Journalise l'ID utilisateur + email utilisé
        $this->audit->log(
            action: 'LOGIN_SUCCESS',
            resource: 'user',
            resourceId: $resourceId,
            data: [
                'email' => $email,
            ]
        );
    }

    public function onLoginFailure(AuthenticationFailureEvent $event): void
    {
        $exception = $event->getException();
        $payload = [];
        if ($request = $event->getRequest()) {
            try {
                $payload = $request->toArray();
            } catch (\Throwable) {
                // toArray() peut lancer une exception si le body n'est pas JSON → on ignore
            }
        }
        $email = $payload['email'] ?? null;

        // Ici on n'a pas d'entité User mais on garde le login tenté + le message
        $this->audit->log(
            action: 'LOGIN_FAILURE',
            resource: 'user',
            data: [
                'email' => $email,
                'error' => $exception?->getMessage(),
            ]
        );
    }
}
