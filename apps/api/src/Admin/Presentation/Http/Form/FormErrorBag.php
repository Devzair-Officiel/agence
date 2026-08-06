<?php

declare(strict_types=1);

namespace App\Admin\Presentation\Http\Form;

/**
 * Petit conteneur d'erreurs par champ + erreurs globales. Utilisé par les
 * contrôleurs admin pour transporter les messages jusqu'au template Twig
 * sans dépendre du composant Form (voir docstring `ArticleFormPayload`).
 *
 * Les templates lisent :
 *   {% for message in errors.for('title') %}<span class="error">{{ message }}</span>{% endfor %}
 *   {% for message in errors.global() %}<div class="alert-error">{{ message }}</div>{% endfor %}
 */
final class FormErrorBag
{
    /**
     * @var array<string, list<string>>
     */
    private array $fields = [];

    /**
     * @var list<string>
     */
    private array $global = [];

    public function addField(string $field, string $message): void
    {
        $this->fields[$field] ??= [];
        $this->fields[$field][] = $message;
    }

    public function addGlobal(string $message): void
    {
        $this->global[] = $message;
    }

    public function hasErrors(): bool
    {
        return $this->global !== [] || $this->fields !== [];
    }

    /**
     * @return list<string>
     */
    public function for(string $field): array
    {
        return $this->fields[$field] ?? [];
    }

    /**
     * @return list<string>
     */
    public function global(): array
    {
        return $this->global;
    }
}
