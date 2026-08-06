<?php

declare(strict_types=1);

namespace App\Admin\Infrastructure\Security;

use App\Admin\Domain\AdminUser;
use App\Admin\Domain\AdminUserRepositoryInterface;
use Psr\Clock\ClockInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

/**
 * Met à jour `last_login_at` après un login réussi et journalise l'événement
 * sur le canal `admin` (sans PII : uniquement l'identifiant admin + résultat).
 *
 * On écoute `InteractiveLoginEvent` (émis par form_login après une
 * authentification interactive) plutôt que `LoginSuccessEvent` pour ne pas
 * réagir à un simple rafraîchissement de session. La distinction est
 * documentée par Symfony (`InteractiveLoginEvent` = « premier passage du
 * formulaire de login »).
 */
#[AsEventListener(event: InteractiveLoginEvent::class)]
final class RecordSuccessfulLoginListener
{
    public function __construct(
        private readonly AdminUserRepositoryInterface $repository,
        private readonly ClockInterface $clock,
        private readonly LoggerInterface $adminLogger,
    ) {
    }

    public function __invoke(InteractiveLoginEvent $event): void
    {
        $user = $event->getAuthenticationToken()->getUser();
        if (!$user instanceof AdminUser) {
            return;
        }

        $now = \DateTimeImmutable::createFromInterface($this->clock->now());
        $user->recordSuccessfulLogin($now);
        $this->repository->save($user);

        $this->adminLogger->info('admin.login_success', [
            'admin_id' => (string) $user->id(),
        ]);
    }
}
