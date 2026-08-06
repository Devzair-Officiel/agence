<?php

declare(strict_types=1);

namespace App\Admin\Infrastructure\Security;

use App\Admin\Domain\AdminUser;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Event\LoginFailureEvent;
use Symfony\Component\Security\Http\Event\LogoutEvent;

/**
 * Journalise les événements de sécurité admin sur le canal `admin`.
 *
 * Aucun email, aucun IP, aucune valeur utilisateur brute n'est enregistrée.
 * Seul le nom court de l'événement d'erreur (classe de l'exception) est
 * consigné pour distinguer bad_credentials / disabled / throttled, sans
 * révéler qu'un compte existe (ou non) pour un email donné.
 *
 * Le succès est traité par `RecordSuccessfulLoginListener` (qui a besoin de
 * l'`AdminUser` pour mettre à jour `last_login_at`).
 */
final class AdminAuditListener
{
    public function __construct(
        private readonly LoggerInterface $adminLogger,
    ) {
    }

    #[AsEventListener(event: LoginFailureEvent::class)]
    public function onLoginFailure(LoginFailureEvent $event): void
    {
        $exception = $event->getException();

        // On expose la classe courte (BadCredentialsException, DisabledException,
        // TooManyLoginAttemptsAuthenticationException, ...) — c'est déjà une
        // information catégorielle et non identifiante.
        $reason = (new \ReflectionClass($exception))->getShortName();

        $context = ['reason' => $reason];

        // Cas particulier : messages custom passés via throttling. On garde
        // le libellé du template mais surtout pas le user identifier.
        if ($exception instanceof CustomUserMessageAuthenticationException) {
            $context['message_key'] = $exception->getMessageKey();
        }

        $this->adminLogger->warning('admin.login_failure', $context);
    }

    #[AsEventListener(event: LogoutEvent::class)]
    public function onLogout(LogoutEvent $event): void
    {
        $token = $event->getToken();
        $user = $token?->getUser();
        $context = [];
        if ($user instanceof AdminUser) {
            $context['admin_id'] = (string) $user->id();
        }

        $this->adminLogger->info('admin.logout', $context);
    }
}
