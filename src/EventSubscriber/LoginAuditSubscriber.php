<?php

namespace App\EventSubscriber;

use App\Service\AuditLoggerService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationFailureEvent;

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
        $user = $event->getUser();

        // Journalise l'ID utilisateur + email utilisé
        $this->audit->log(
            action: 'LOGIN_SUCCESS',
            resource: 'user',
            resourceId: $user?->getId(),
            data: [
                'email' => $user->getUserIdentifier()
            ]
        );
    }

    public function onLoginFailure(AuthenticationFailureEvent $event): void
    {
        $exception = $event->getException();
        $token = $event->getToken();

        // Ici on n'a pas d'entité User mais on garde le login tenté + le message
        $this->audit->log(
            action: 'LOGIN_FAILURE',
            resource: 'user',
            data: [
                'email' => $token?->getUser(),
                'error' => $exception?->getMessage(),
            ]
        );
    }
}
