<?php

declare(strict_types=1);

namespace Doctrine\Tests\ORM\Functional\ValueConversionType;

use Doctrine\Tests\Models\ValueConversionType as Entity;
use Doctrine\Tests\OrmFunctionalTestCase;

/**
 * The entities all use a custom type that converst the value as identifier(s).
 * {@see \Doctrine\Tests\DbalTypes\Rot13Type}
 *
 * Test that OneToMany associations work correctly, focusing on EXTRA_LAZY
 * functionality.
 *
 * @group DDC-3380
 */
class OneToManyExtraLazyTest extends OrmFunctionalTestCase
{
    public function setUp()
    {
        $this->useModelSet('vct_onetomany_extralazy');

        parent::setUp();

        $inversed = new Entity\InversedOneToManyExtraLazyEntity();
        $inversed->id1 = 'abc';

        $owning1 = new Entity\OwningManyToOneExtraLazyEntity();
        $owning1->id2 = 'def';

        $owning2 = new Entity\OwningManyToOneExtraLazyEntity();
        $owning2->id2 = 'ghi';

        $owning3 = new Entity\OwningManyToOneExtraLazyEntity();
        $owning3->id2 = 'jkl';

        $inversed->associatedEntities->add($owning1);
        $owning1->associatedEntity = $inversed;
        $inversed->associatedEntities->add($owning2);
        $owning2->associatedEntity = $inversed;
        $inversed->associatedEntities->add($owning3);
        $owning3->associatedEntity = $inversed;

        $this->em->persist($inversed);
        $this->em->persist($owning1);
        $this->em->persist($owning2);
        $this->em->persist($owning3);

        $this->em->flush();
        $this->em->clear();
    }

    public function testThatExtraLazyCollectionIsCounted()
    {
        $inversed = $this->em->find(Entity\InversedOneToManyExtraLazyEntity::class, 'abc');

        self::assertEquals(3, $inversed->associatedEntities->count());
    }

    public function testThatExtraLazyCollectionContainsAnEntity()
    {
        $inversed = $this->em->find(Entity\InversedOneToManyExtraLazyEntity::class, 'abc');
        $owning   = $this->em->find(Entity\OwningManyToOneExtraLazyEntity::class, 'def');

        self::assertTrue($inversed->associatedEntities->contains($owning));
    }

    public function testThatExtraLazyCollectionContainsAnIndexbyKey()
    {
        $inversed = $this->em->find(Entity\InversedOneToManyExtraLazyEntity::class, 'abc');

        self::assertTrue($inversed->associatedEntities->containsKey('def'));
    }

    public function testThatASliceOfTheExtraLazyCollectionIsLoaded()
    {
        $inversed = $this->em->find(Entity\InversedOneToManyExtraLazyEntity::class, 'abc');

        self::assertCount(2, $inversed->associatedEntities->slice(0, 2));
    }
}
