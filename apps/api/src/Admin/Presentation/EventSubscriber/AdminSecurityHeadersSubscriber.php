<?php

declare(strict_types=1);

namespace App\Admin\Presentation\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Injecte les en-têtes de sécurité sur toutes les réponses `/admin/*`.
 *
 * Phase 8C1 : aucune ressource externe, aucun JavaScript, styles servis
 * en fichier statique dédié (`/admin/assets/admin.css`, exclu du firewall
 * par `access_control` — voir ADR-012). La CSP est donc très stricte :
 *   default-src 'none' ; script-src 'none' ; style-src 'self' ;
 *   img-src 'self' data: ; font-src 'self' ; form-action 'self' ;
 *   base-uri 'none' ; frame-ancestors 'none'.
 *
 * `'unsafe-inline'` a été retiré de `style-src` en fin de Phase 8C1 :
 * aucun besoin technique documenté ne le justifie (aucun style dynamique).
 * Toute réintroduction d'inline styles doit passer par un mécanisme de
 * nonce et faire l'objet d'une nouvelle ADR.
 *
 * Phase 8C3 : ajout d'un `Cache-Control` strict pour interdire à tout
 * intermédiaire (navigateur, reverse proxy) de mémoriser les pages d'admin.
 * Le contenu est spécifique à l'admin authentifié, contient parfois des
 * jetons CSRF fraîchement générés et n'a pas vocation à être resservi.
 *
 * Toutes les autres réponses (site public, API contact, ressources) ne sont
 * pas touchées : leur politique est portée respectivement par Nuxt et par
 * les listeners Contact/Editorial existants.
 */
final class AdminSecurityHeadersSubscriber implements EventSubscriberInterface
{
    private const HEADERS = [
        'Content-Security-Policy' => "default-src 'none'; script-src 'none'; style-src 'self'; img-src 'self' data:; font-src 'self'; form-action 'self'; base-uri 'none'; frame-ancestors 'none'",
        'X-Content-Type-Options' => 'nosniff',
        'X-Frame-Options' => 'DENY',
        'Referrer-Policy' => 'no-referrer',
        'Permissions-Policy' => 'camera=(), microphone=(), geolocation=(), interest-cohort=()',
        'Cross-Origin-Opener-Policy' => 'same-origin',
        'Cross-Origin-Resource-Policy' => 'same-origin',
        'X-Robots-Tag' => 'noindex, nofollow',
        'Cache-Control' => 'private, no-store, no-cache, must-revalidate',
        'Pragma' => 'no-cache',
    ];

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::RESPONSE => ['onKernelResponse', -10],
        ];
    }

    public function onKernelResponse(ResponseEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $path = $event->getRequest()->getPathInfo();
        if (!str_starts_with($path, '/admin')) {
            return;
        }

        $response = $event->getResponse();
        foreach (self::HEADERS as $name => $value) {
            $response->headers->set($name, $value);
        }
    }
}
