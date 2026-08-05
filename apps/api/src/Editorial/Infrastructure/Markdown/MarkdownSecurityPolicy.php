<?php

declare(strict_types=1);

namespace App\Editorial\Infrastructure\Markdown;

use League\CommonMark\Environment\Environment;
use League\CommonMark\Environment\EnvironmentInterface;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\Table\TableExtension;

/**
 * Construit un `Environment` CommonMark sécurisé pour l'import d'articles.
 *
 * Décisions figées (voir ADR-010) :
 * - `html_input = strip` : le HTML brut n'est pas rendu (défense en profondeur ;
 *   la première ligne de défense reste `MarkdownContentValidator` qui refuse
 *   explicitement à l'import) ;
 * - `allow_unsafe_links = false` : les schémas dangereux
 *   (`javascript:`, `data:`, `vbscript:`) sont neutralisés au rendu par
 *   CommonMark, en complément du rejet explicite à l'import ;
 * - `max_nesting_level` : borne la profondeur d'imbrication pour éviter le
 *   parsing pathologique de listes/citations profondément imbriquées ;
 * - `max_delimiters_per_line` : borne les tirets/étoiles d'emphase pour
 *   éviter le comportement quadratique documenté par CommonMark sur les
 *   lignes très longues.
 *
 * `DisallowedRawHtmlExtension` n'est PAS ajoutée : elle rend le HTML dans un
 * état échappé qui pollue le rendu ; on préfère refuser à l'import.
 */
final class MarkdownSecurityPolicy
{
    /**
     * Profondeur maximale d'imbrication de blocs. 15 laisse largement la
     * place à un article normal (liste de listes, citation dans citation),
     * tout en refusant le contenu manifestement pathologique.
     */
    public const MAX_NESTING_LEVEL = 15;

    /**
     * Nombre maximal de délimiteurs d'emphase par ligne. Une ligne d'article
     * dépasse rarement 5–10 ; 100 est confortable pour les listes de mots
     * en italique dans un même paragraphe sans laisser passer les attaques
     * de complexité quadratique.
     */
    public const MAX_DELIMITERS_PER_LINE = 100;

    public function environment(): EnvironmentInterface
    {
        $env = new Environment([
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
            'max_nesting_level' => self::MAX_NESTING_LEVEL,
            'max_delimiters_per_line' => self::MAX_DELIMITERS_PER_LINE,
            'renderer' => [
                'soft_break' => "\n",
            ],
        ]);
        $env->addExtension(new CommonMarkCoreExtension());
        $env->addExtension(new TableExtension());

        return $env;
    }
}
