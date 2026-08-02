<?php

declare(strict_types=1);

namespace App\Contact\Configuration;

/**
 * Résultat immuable de {@see ContactConfigurationValidator::validate()}.
 *
 * Contient la liste des anomalies. `isValid()` répond « oui » si aucune
 * issue de sévérité ERROR n'a été trouvée : les WARNINGS n'interdisent pas
 * le fonctionnement mais méritent d'être affichés à l'ops.
 */
final readonly class ContactConfigurationReport
{
    /**
     * @param list<ContactConfigurationIssue> $issues
     */
    public function __construct(
        public array $issues,
    ) {
    }

    public function isValid(): bool
    {
        foreach ($this->issues as $issue) {
            if ($issue->isError()) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return list<ContactConfigurationIssue>
     */
    public function errors(): array
    {
        return array_values(array_filter($this->issues, static fn (ContactConfigurationIssue $i) => $i->isError()));
    }

    /**
     * @return list<ContactConfigurationIssue>
     */
    public function warnings(): array
    {
        return array_values(array_filter($this->issues, static fn (ContactConfigurationIssue $i) => !$i->isError()));
    }
}
