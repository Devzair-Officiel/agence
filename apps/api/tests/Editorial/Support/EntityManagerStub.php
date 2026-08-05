<?php

declare(strict_types=1);

namespace App\Tests\Editorial\Support;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Trait fournissant un `EntityManagerInterface` mock aux tests de handlers.
 *
 * On expose deux attentes possibles :
 * - `flush()` exactement une fois (chemins d'écriture nominaux) ;
 * - `flush()` jamais (dry-run et chemins d'échec).
 *
 * Le trait plutôt qu'une classe statique : `getMockBuilder()` est protégé,
 * il doit être appelé depuis le scope du test.
 */
trait EntityManagerStub
{
    /**
     * @return EntityManagerInterface&MockObject
     */
    private function entityManagerExpectingFlush(): EntityManagerInterface
    {
        $em = $this->getMockBuilder(EntityManagerInterface::class)->getMock();
        $em->expects(self::once())->method('flush');

        return $em;
    }

    /**
     * @return EntityManagerInterface&MockObject
     */
    private function entityManagerExpectingNoFlush(): EntityManagerInterface
    {
        $em = $this->getMockBuilder(EntityManagerInterface::class)->getMock();
        $em->expects(self::never())->method('flush');

        return $em;
    }
}
