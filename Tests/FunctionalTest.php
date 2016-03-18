<?php
/**
 * This file is part of DoctrineRestDriver.
 *
 * DoctrineRestDriver is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * DoctrineRestDriver is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with DoctrineRestDriver.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Circle\DoctrineRestDriver\Tests;

use Circle\DoctrineRestDriver\Tests\Entity\AssociatedEntity;
use Circle\DoctrineRestDriver\Tests\Entity\TestEntity;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\ORM\Query\ResultSetMapping;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Doctrine\ORM\EntityManager;

/**
 * Tests against a mock api
 *
 * @author    Tobias Hauck <tobias@circle.ai>
 * @copyright 2015 TeeAge-Beatz UG
 *
 * @SuppressWarnings("PHPMD.TooManyPublicMethods")
 * @SuppressWarnings("PHPMD.StaticAccess")
 */
class FunctionalTest extends WebTestCase {

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * {@inheritdoc}
     */
    public function setUp() {
        AnnotationRegistry::registerFile(__DIR__ . '/../vendor/doctrine/orm/lib/Doctrine/ORM/Mapping/Entity.php');
        AnnotationRegistry::registerFile(__DIR__ . '/../vendor/doctrine/orm/lib/Doctrine/ORM/Mapping/Table.php');
        AnnotationRegistry::registerFile(__DIR__ . '/../vendor/doctrine/orm/lib/Doctrine/ORM/Mapping/Column.php');
        AnnotationRegistry::registerFile(__DIR__ . '/../vendor/doctrine/orm/lib/Doctrine/ORM/Mapping/Id.php');
        AnnotationRegistry::registerFile(__DIR__ . '/../vendor/doctrine/orm/lib/Doctrine/ORM/Mapping/GeneratedValue.php');
        AnnotationRegistry::registerFile(__DIR__ . '/../vendor/doctrine/orm/lib/Doctrine/ORM/Mapping/OneToMany.php');
        AnnotationRegistry::registerFile(__DIR__ . '/../vendor/doctrine/orm/lib/Doctrine/ORM/Mapping/ManyToOne.php');

        static::bootKernel();
        $this->em = static::$kernel->getContainer()->get('doctrine.orm.default_entity_manager');
    }

    /**
     * @test
     * @group  functional
     * @covers Circle\DoctrineRestDriver\Driver
     * @covers Circle\DoctrineRestDriver\Connection
     * @covers Circle\DoctrineRestDriver\Statement
     * @covers Circle\DoctrineRestDriver\Statement::<private>
     * @covers Circle\DoctrineRestDriver\Events\BeforeRequestEvent
     * @covers Circle\DoctrineRestDriver\Events\Subscribers\BasicHttpAuthentication
     */
    public function find() {
        $entity = $this->em->find('Circle\DoctrineRestDriver\Tests\Entity\TestEntity', 1);
        $this->assertSame(1,         $entity->getId());
        $this->assertSame('MyName',  $entity->getName());
        $this->assertSame('MyValue', $entity->getValue());
    }

    /**
     * @test
     * @group  functional
     * @covers Circle\DoctrineRestDriver\Driver
     * @covers Circle\DoctrineRestDriver\Connection
     * @covers Circle\DoctrineRestDriver\Statement
     * @covers Circle\DoctrineRestDriver\Statement::<private>
     * @covers Circle\DoctrineRestDriver\Events\BeforeRequestEvent
     * @covers Circle\DoctrineRestDriver\Events\Subscribers\BasicHttpAuthentication
     * @expectedException \Exception
     */
    public function findNonExisting() {
        $this->em->find('Circle\DoctrineRestDriver\Tests\Entity\TestEntity', 2);
    }

    /**
     * @test
     * @group  functional
     * @covers Circle\DoctrineRestDriver\Driver
     * @covers Circle\DoctrineRestDriver\Connection
     * @covers Circle\DoctrineRestDriver\Statement
     * @covers Circle\DoctrineRestDriver\Statement::<private>
     * @covers Circle\DoctrineRestDriver\Events\BeforeRequestEvent
     * @covers Circle\DoctrineRestDriver\Events\Subscribers\BasicHttpAuthentication
     */
    public function findOneBy() {
        $entity = $this->em->getRepository('Circle\DoctrineRestDriver\Tests\Entity\TestEntity')->findOneBy(['id' => 1]);
        $this->assertSame(1,          $entity->getId());
        $this->assertSame('MyName',   $entity->getName());
        $this->assertSame('MyValue',  $entity->getValue());
    }

    /**
     * @test
     * @group  functional
     * @covers Circle\DoctrineRestDriver\Driver
     * @covers Circle\DoctrineRestDriver\Connection
     * @covers Circle\DoctrineRestDriver\Statement
     * @covers Circle\DoctrineRestDriver\Statement::<private>
     * @covers Circle\DoctrineRestDriver\Events\BeforeRequestEvent
     * @covers Circle\DoctrineRestDriver\Events\Subscribers\BasicHttpAuthentication
     */
    public function findBy() {
        $entity = $this->em->getRepository('Circle\DoctrineRestDriver\Tests\Entity\TestEntity')->findBy(['id' => 1]);
        $this->assertTrue(is_array($entity));
        $this->assertSame(1,          $entity[0]->getId());
        $this->assertSame('MyName',   $entity[0]->getName());
        $this->assertSame('MyValue',  $entity[0]->getValue());
    }

    /**
     * @test
     * @group  functional
     * @covers Circle\DoctrineRestDriver\Driver
     * @covers Circle\DoctrineRestDriver\Connection
     * @covers Circle\DoctrineRestDriver\Statement
     * @covers Circle\DoctrineRestDriver\Statement::<private>
     * @covers Circle\DoctrineRestDriver\Events\BeforeRequestEvent
     * @covers Circle\DoctrineRestDriver\Events\Subscribers\BasicHttpAuthentication
     */
    public function findAll() {
        $entity = $this->em->getRepository('Circle\DoctrineRestDriver\Tests\Entity\TestEntity')->findAll();
        $this->assertTrue(is_array($entity));

        $this->assertSame(1,         $entity[0]->getId());
        $this->assertSame('MyName',  $entity[0]->getName());
        $this->assertSame('MyValue', $entity[0]->getValue());

        $this->assertSame(2,           $entity[1]->getId());
        $this->assertSame('NextName',  $entity[1]->getName());
        $this->assertSame('NextValue', $entity[1]->getValue());
    }

    /**
     * @test
     * @group  functional
     * @covers Circle\DoctrineRestDriver\Driver
     * @covers Circle\DoctrineRestDriver\Connection
     * @covers Circle\DoctrineRestDriver\Statement
     * @covers Circle\DoctrineRestDriver\Statement::<private>
     * @covers Circle\DoctrineRestDriver\Events\BeforeRequestEvent
     * @covers Circle\DoctrineRestDriver\Events\Subscribers\BasicHttpAuthentication
     */
    public function persistAndFlush() {
        $entity = new TestEntity();
        $entity->setName('MyName');
        $entity->setValue('MyValue');

        $associatedEntity = new AssociatedEntity();
        $entity->addCategory($associatedEntity);

        $this->em->persist($associatedEntity);
        $this->em->persist($entity);
        $this->em->flush();

        $this->assertSame(1,         $entity->getId());
        $this->assertSame('MyName',  $entity->getName());
        $this->assertSame('MyValue', $entity->getValue());
        $this->assertSame(1,         $entity->getCategories()->first()->getId());
    }

    /**
     * @test
     * @group  functional
     * @covers Circle\DoctrineRestDriver\Driver
     * @covers Circle\DoctrineRestDriver\Connection
     * @covers Circle\DoctrineRestDriver\Statement
     * @covers Circle\DoctrineRestDriver\Statement::<private>
     * @covers Circle\DoctrineRestDriver\Events\BeforeRequestEvent
     * @covers Circle\DoctrineRestDriver\Events\Subscribers\BasicHttpAuthentication
     */
    public function updateAndFlush() {
        $entity = $this->em->find('Circle\DoctrineRestDriver\Tests\Entity\TestEntity', 1);
        $entity->setName('newName');
        $this->em->flush();

        $this->assertSame(1,         $entity->getId());
        $this->assertSame('newName', $entity->getName());
        $this->assertSame('MyValue', $entity->getValue());
    }

    /**
     * @test
     * @group  functional
     * @covers Circle\DoctrineRestDriver\Driver
     * @covers Circle\DoctrineRestDriver\Connection
     * @covers Circle\DoctrineRestDriver\Statement
     * @covers Circle\DoctrineRestDriver\Statement::<private>
     * @covers Circle\DoctrineRestDriver\Events\BeforeRequestEvent
     * @covers Circle\DoctrineRestDriver\Events\Subscribers\BasicHttpAuthentication
     */
    public function remove() {
        $entity = $this->em->find('Circle\DoctrineRestDriver\Tests\Entity\TestEntity', 1);
        $this->em->remove($entity);
        $this->em->flush();
    }

    /**
     * @test
     * @group  functional
     * @covers Circle\DoctrineRestDriver\Driver
     * @covers Circle\DoctrineRestDriver\Connection
     * @covers Circle\DoctrineRestDriver\Statement
     * @covers Circle\DoctrineRestDriver\Statement::<private>
     * @covers Circle\DoctrineRestDriver\Events\BeforeRequestEvent
     * @covers Circle\DoctrineRestDriver\Events\Subscribers\BasicHttpAuthentication
     */
    public function dql() {
        $entity = $this->em->createQuery('SELECT p FROM Circle\DoctrineRestDriver\Tests\Entity\TestEntity p WHERE p.id = 1')
            ->getSingleResult();

        $this->assertSame(1,         $entity->getId());
        $this->assertSame('MyName',  $entity->getName());
        $this->assertSame('MyValue', $entity->getValue());
    }

    /**
     * @test
     * @group  functional
     * @covers Circle\DoctrineRestDriver\Driver
     * @covers Circle\DoctrineRestDriver\Connection
     * @covers Circle\DoctrineRestDriver\Statement
     * @covers Circle\DoctrineRestDriver\Statement::<private>
     * @covers Circle\DoctrineRestDriver\Events\BeforeRequestEvent
     * @covers Circle\DoctrineRestDriver\Events\Subscribers\BasicHttpAuthentication
     */
    public function nativeQuery() {
        $mapping = new ResultSetMapping();
        $mapping->addEntityResult('Circle\DoctrineRestDriver\Tests\Entity\TestEntity', 'products');
        $mapping->addFieldResult('products', 'id', 'id');
        $mapping->addFieldResult('products', 'name', 'name');
        $entity = $this->em->createNativeQuery('SELECT id, name FROM products', $mapping)->getResult();

        $this->assertSame(1,        $entity[0]->getId());
        $this->assertSame('MyName', $entity[0]->getName());
        $this->assertSame(null,     $entity[0]->getValue());

        $this->assertSame(2,          $entity[1]->getId());
        $this->assertSame('NextName', $entity[1]->getName());
        $this->assertSame(null,       $entity[1]->getValue());
    }

    /**
     * @test
     * @group  functional
     * @covers Circle\DoctrineRestDriver\Driver
     * @covers Circle\DoctrineRestDriver\Connection
     * @covers Circle\DoctrineRestDriver\Statement
     * @covers Circle\DoctrineRestDriver\Statement::<private>
     * @covers Circle\DoctrineRestDriver\Events\BeforeRequestEvent
     * @covers Circle\DoctrineRestDriver\Events\Subscribers\BasicHttpAuthentication
     */
    public function dqlWithOrderBy() {
        $entity = $this->em->createQuery('SELECT p FROM Circle\DoctrineRestDriver\Tests\Entity\TestEntity p ORDER BY p.name DESC')
            ->getResult();

        $this->assertSame(2,           $entity[0]->getId());
        $this->assertSame('NextName',  $entity[0]->getName());
        $this->assertSame('NextValue', $entity[0]->getValue());

        $this->assertSame(1,         $entity[1]->getId());
        $this->assertSame('MyName',  $entity[1]->getName());
        $this->assertSame('MyValue', $entity[1]->getValue());
    }

    /**
     * @test
     * @group  functional
     * @covers Circle\DoctrineRestDriver\Driver
     * @covers Circle\DoctrineRestDriver\Connection
     * @covers Circle\DoctrineRestDriver\Statement
     * @covers Circle\DoctrineRestDriver\Statement::<private>
     * @covers Circle\DoctrineRestDriver\Events\BeforeRequestEvent
     * @covers Circle\DoctrineRestDriver\Events\Subscribers\BasicHttpAuthentication
     */
    public function dqlWithObjectParameter() {
        $entity = $this->em->createQuery('SELECT p FROM Circle\DoctrineRestDriver\Tests\Entity\TestEntity p WHERE p.name = ?1')
            ->setParameter(1, new \DateTime())
            ->getResult();

        $this->assertSame(2, count($entity));
    }

    /**
     * @test
     * @group  functional
     * @covers Circle\DoctrineRestDriver\Driver
     * @covers Circle\DoctrineRestDriver\Connection
     * @covers Circle\DoctrineRestDriver\Statement
     * @covers Circle\DoctrineRestDriver\Statement::<private>
     * @covers Circle\DoctrineRestDriver\Events\BeforeRequestEvent
     * @covers Circle\DoctrineRestDriver\Events\Subscribers\BasicHttpAuthentication
     * @expectedException \Exception
     */
    public function nonImplementedEntity() {
        $this->em->find('Circle\DoctrineRestDriver\Entity\NonImplementedEntity', 1);
    }
}
