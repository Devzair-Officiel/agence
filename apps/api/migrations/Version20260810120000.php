<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Phase 9B — Rattachement d'une image principale à un `Article`.
 *
 * Ajoute deux colonnes nullables à `editorial_article` :
 *   - `hero_media_id` UUID NULL : référence stable vers un `MediaAsset`
 *     du contexte borné `EditorialMedia`. **Aucune FK au niveau DB** :
 *     Doctrine ORM ne peut pas représenter une FK sur colonne scalaire
 *     sans introduire une association ORM `#[ManyToOne]` — laquelle
 *     coupleait `Article` (Editorial) à `MediaAsset` (EditorialMedia),
 *     violant l'isolation entre contextes bornés. L'intégrité
 *     référentielle inter-contextes est assurée au niveau Application :
 *     `SetDraftArticleHeroImageHandler` valide l'existence via
 *     `MediaAssetLookupInterface::exists($uuid)` avant mutation, et
 *     toute future suppression de média passera par la même frontière
 *     applicative (la Phase 9B n'introduit pas de suppression). Aucune
 *     contrainte d'unicité : un média peut être partagé par plusieurs
 *     articles (cf. test « shared media stays 200 while one archived »).
 *   - `hero_image_alt` VARCHAR(300) NULL : texte alternatif éditorial,
 *     obligatoire dès qu'un média est rattaché (invariant porté par le
 *     domaine ET redoublé par la CHECK constraint ci-dessous). Longueur
 *     alignée sur l'invariant du VO `ArticleHeroImage` (1..300 caractères).
 *
 * Ajoute une CHECK constraint de cohérence PAIR : les deux colonnes sont
 * soit toutes deux NULL, soit toutes deux non-NULL. C'est une défense en
 * profondeur — l'agrégat `Article` garantit déjà cet invariant, mais la
 * base doit refuser tout état incohérent qui aurait pu être produit par
 * une écriture ORM bypassée ou un correctif DBA manuel.
 *
 * Ajoute un index sur `hero_media_id` : accélère la lecture inverse (« quels
 * articles utilisent ce média ? »), requise par la route publique média
 * `GET /media/{id}` du Checkpoint 2 (autorisation basée sur l'existence
 * d'un article Published référençant l'asset).
 *
 * Pas de migration de données : les articles existants restent avec
 * `hero_media_id IS NULL AND hero_image_alt IS NULL` — état valide selon
 * l'invariant.
 *
 * `down()` retire l'index, la CHECK constraint puis les colonnes. Aucune
 * migration destructive : la migration 9A reste intacte, seule
 * `editorial_article` est modifiée.
 */
final class Version20260810120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Phase 9B — Ajoute hero_media_id + hero_image_alt à editorial_article (intégrité inter-contextes portée par Application, jamais par FK DB).';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE editorial_article ADD hero_media_id UUID DEFAULT NULL');
        $this->addSql('ALTER TABLE editorial_article ADD hero_image_alt VARCHAR(300) DEFAULT NULL');

        $this->addSql(<<<'SQL'
            ALTER TABLE editorial_article
            ADD CONSTRAINT check_editorial_article_hero_image_pair
            CHECK (
                (hero_media_id IS NULL AND hero_image_alt IS NULL)
                OR
                (hero_media_id IS NOT NULL AND hero_image_alt IS NOT NULL)
            )
        SQL);

        $this->addSql('CREATE INDEX idx_editorial_article_hero_media_id ON editorial_article (hero_media_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX idx_editorial_article_hero_media_id');
        $this->addSql('ALTER TABLE editorial_article DROP CONSTRAINT check_editorial_article_hero_image_pair');
        $this->addSql('ALTER TABLE editorial_article DROP hero_image_alt');
        $this->addSql('ALTER TABLE editorial_article DROP hero_media_id');
    }
}
