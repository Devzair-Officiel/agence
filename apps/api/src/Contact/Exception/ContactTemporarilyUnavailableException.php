<?php

declare(strict_types=1);

namespace App\Contact\Exception;

/**
 * Levée quand le transport e-mail ne peut pas remettre le message.
 *
 * Cas typiques : le SMTP OVHcloud refuse l'authentification, coupe la
 * connexion en cours d'envoi, ou le destinataire n'est pas configuré. Le
 * contrôleur traduit cette exception en HTTP 503 `temporary_error` — jamais
 * en 202/200 : l'utilisateur doit savoir que son message n'a pas été remis.
 *
 * Aucun contenu du message d'exception n'atteint la réponse HTTP ni les logs
 * publics : le pipeline utilise cette exception uniquement pour brancher la
 * réponse 503 et logger l'événement `contact.mailer_unavailable` sans PII.
 */
final class ContactTemporarilyUnavailableException extends \RuntimeException
{
}
