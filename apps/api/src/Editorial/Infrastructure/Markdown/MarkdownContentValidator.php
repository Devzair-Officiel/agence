<?php

declare(strict_types=1);

namespace App\Editorial\Infrastructure\Markdown;

use App\Editorial\Application\Markdown\MarkdownValidationException;
use League\CommonMark\Extension\CommonMark\Node\Block\HtmlBlock;
use League\CommonMark\Extension\CommonMark\Node\Inline\HtmlInline;
use League\CommonMark\Extension\CommonMark\Node\Inline\Image;
use League\CommonMark\Extension\CommonMark\Node\Inline\Link;
use League\CommonMark\Node\Node;
use League\CommonMark\Parser\MarkdownParser;

/**
 * Refus explicite du HTML brut et des URLs à schéma dangereux avant écriture.
 *
 * On parse le Markdown avec l'environnement sécurisé fourni par
 * `MarkdownSecurityPolicy` (le rendu ne sera fait qu'au moment du GET
 * public, en Phase 8B2 pour Nuxt — ici on ne fait que valider). On marche
 * ensuite l'AST pour lever une exception si on rencontre :
 *
 * - un bloc HTML (`<script>`, `<div>`, etc.) même désamorcé par `strip` ;
 * - un HTML inline (`<span>`, `<img …>` HTML brut, etc.) ;
 * - un lien Markdown ou image dont l'URL utilise un schéma non autorisé.
 *
 * L'objectif est de garantir que le stockage en base ne contient jamais du
 * HTML brut auteur — même si le rendu HTML devait un jour changer de
 * politique côté front. La donnée reste propre à la source.
 */
final class MarkdownContentValidator
{
    private const ALLOWED_URL_SCHEMES = [
        'http' => true,
        'https' => true,
        'mailto' => true,
        'tel' => true,
    ];

    private readonly MarkdownParser $parser;

    public function __construct(MarkdownSecurityPolicy $policy)
    {
        $this->parser = new MarkdownParser($policy->environment());
    }

    /**
     * Lève `MarkdownValidationException` si le corps viole la politique.
     */
    public function validate(string $bodyMarkdown): void
    {
        $document = $this->parser->parse($bodyMarkdown);

        $violations = [];
        $walker = $document->walker();

        while (($event = $walker->next()) !== null) {
            if (!$event->isEntering()) {
                continue;
            }

            $node = $event->getNode();

            if ($node instanceof HtmlBlock) {
                $violations[] = 'HTML brut (bloc) interdit : utilisez exclusivement la syntaxe Markdown.';
                continue;
            }
            if ($node instanceof HtmlInline) {
                $violations[] = 'HTML brut (inline) interdit : utilisez exclusivement la syntaxe Markdown.';
                continue;
            }
            if ($node instanceof Link || $node instanceof Image) {
                $violation = $this->assertSafeUrl($node);
                if ($violation !== null) {
                    $violations[] = $violation;
                }
            }
        }

        if ($violations !== []) {
            throw new MarkdownValidationException(
                'Contenu Markdown refusé : '.implode(' ; ', array_unique($violations)),
                array_values(array_unique($violations)),
            );
        }
    }

    private function assertSafeUrl(Node $node): ?string
    {
        \assert($node instanceof Link || $node instanceof Image);
        $url = trim($node->getUrl());

        if ($url === '') {
            return 'URL vide interdite dans un lien ou une image.';
        }

        // URL relative (pas de schéma) → autorisée : c'est un lien interne.
        // On borne quand même à des caractères sûrs.
        if (!preg_match('#^[a-zA-Z][a-zA-Z0-9+\-.]*:#', $url)) {
            return null;
        }

        $colon = strpos($url, ':');
        \assert($colon !== false);
        $scheme = strtolower(substr($url, 0, $colon));

        if (!isset(self::ALLOWED_URL_SCHEMES[$scheme])) {
            return \sprintf(
                'Schéma d\'URL "%s" interdit. Autorisés : %s.',
                $scheme,
                implode(', ', array_keys(self::ALLOWED_URL_SCHEMES)),
            );
        }

        return null;
    }
}
