<?php

declare(strict_types=1);

namespace App\Editorial\Application\Query;

use App\Editorial\Domain\Exception\ArticleNotFoundException;

final class GetAdminArticleForEditHandler
{
    public function __construct(
        private readonly AdminArticleReadRepositoryInterface $repository,
    ) {
    }

    public function __invoke(GetAdminArticleForEdit $query): AdminArticleEditView
    {
        $view = $this->repository->findForEdit($query->id);
        if ($view === null) {
            throw ArticleNotFoundException::forId($query->id->toRfc4122());
        }

        return $view;
    }
}
