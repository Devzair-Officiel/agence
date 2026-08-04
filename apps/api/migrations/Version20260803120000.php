<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Phase 8A — Création de la table `editorial_article`.
 *
 * Contraintes structurantes :
 * - UUID stocké en `uuid` natif Postgres.
 * - Slug unique.
 * - `expertise_ids` en `jsonb` pour permettre des index GIN futurs (Phase 8B+).
 * - Index composite sur (status, published_at) pour accélérer la page publique.
 */
final class Version20260803120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Phase 8A — Crée la table editorial_article (PostgreSQL 17).';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE TABLE editorial_article (
                id UUID NOT NULL,
                slug VARCHAR(120) NOT NULL,
                title VARCHAR(200) NOT NULL,
                excerpt VARCHAR(320) NOT NULL,
                body_markdown TEXT NOT NULL,
                seo_title VARCHAR(70) NOT NULL,
                seo_description VARCHAR(160) NOT NULL,
                author_name VARCHAR(120) NOT NULL,
                author_type VARCHAR(20) NOT NULL,
                status VARCHAR(20) NOT NULL,
                expertise_ids JSONB NOT NULL,
                published_at TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL,
                created_at TIMESTAMP(0) WITH TIME ZONE NOT NULL,
                updated_at TIMESTAMP(0) WITH TIME ZONE NOT NULL,
                PRIMARY KEY(id)
            )
        SQL);

        $this->addSql('CREATE UNIQUE INDEX uniq_editorial_article_slug ON editorial_article (slug)');
        $this->addSql('CREATE INDEX idx_editorial_article_status_published_at ON editorial_article (status, published_at)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE editorial_article');
    }
}
