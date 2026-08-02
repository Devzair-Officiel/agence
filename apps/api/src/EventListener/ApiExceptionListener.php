<?php

declare(strict_types=1);

namespace App\EventListener;

use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\Uid\Uuid;

/**
 * Convertit toute exception non capturée en JSON — l'API ne rend jamais
 * d'HTML. Aucun détail interne n'est exposé au client. Le message et la
 * trace sont loggués côté serveur avec un request_id de corrélation.
 */
#[AsEventListener(event: ExceptionEvent::class, priority: 0)]
final class ApiExceptionListener
{
    public function __construct(
        private readonly LoggerInterface $contactLogger,
        private readonly bool $debug,
    ) {
    }

    public function __invoke(ExceptionEvent $event): void
    {
        $throwable = $event->getThrowable();
        $status = $throwable instanceof HttpExceptionInterface
            ? $throwable->getStatusCode()
            : Response::HTTP_INTERNAL_SERVER_ERROR;

        $requestId = $event->getRequest()->headers->get('X-Request-Id')
            ?? Uuid::v7()->toRfc4122();

        $this->contactLogger->error('contact.unhandled_exception', [
            'request_id' => $requestId,
            'status' => $status,
            'exception' => $throwable::class,
            'message' => $throwable->getMessage(),
        ]);

        $payload = [
            'status' => 'error',
            'code' => 'server_error',
            'request_id' => $requestId,
        ];

        if ($this->debug) {
            $payload['debug'] = [
                'exception' => $throwable::class,
                'message' => $throwable->getMessage(),
            ];
        }

        $response = new JsonResponse($payload, $status);
        $response->headers->set('X-Request-Id', $requestId);
        $response->headers->set('Cache-Control', 'no-store');

        $event->setResponse($response);
    }
}
