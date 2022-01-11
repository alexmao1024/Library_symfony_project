<?php

namespace App\Tests\IntegrationTest;

use App\Entity\AdminUser;
use App\Entity\Book;
use App\Factory\Factory;
use App\Factory\BookFactory;
use App\Repository\AdminUserRepository;
use App\Repository\BookRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class EntityManagerTest extends KernelTestCase
{
    private $entityManager;

    public function setUp(): void
    {
        self::bootKernel();

        $this->entityManager = static::getContainer()->get('doctrine.orm.entity_manager');

        $this->truncateEntities([
            Book::class,
            AdminUser::class
        ]);
    }

    public function testBookEntityManager(): void
    {

        $bookFactory = static::getContainer()->get(BookFactory::class);
        $this->assertInstanceOf(BookFactory::class,$bookFactory);
        $book1 = $bookFactory->create('刘慈欣','三体1','科幻世界',101.0,5);
        $book2 = $bookFactory->create('刘慈欣','三体2','科幻世界',105.0,5);
        $book3 = $bookFactory->create('刘慈欣','三体3','世科幻界',150.0,5);
        $book4 = $bookFactory->create('刘慈欣','三体4','科幻世界',120.0,5);
        $book5 = $bookFactory->create('刘慈欣','三体5','科幻世界',100.0,5);

        $this->entityManager->persist($book1);
        $this->entityManager->persist($book2);
        $this->entityManager->persist($book3);
        $this->entityManager->persist($book4);
        $this->entityManager->persist($book5);

        $this->entityManager->flush();

        $bookRepo = static::getContainer()->get(BookRepository::class);

        $this->assertInstanceOf(BookRepository::class,$bookRepo);

        $books = $bookRepo->findAll();
        $this->assertCount(5,$books);

        $findOneByBook = $bookRepo->findOneBy(['press'=>'世科幻界']);
        $this->assertSame($book3,$findOneByBook);

        $findBook = $bookRepo->find($findOneByBook->getId());
        $this->assertSame($findOneByBook,$findBook);

        $findByBooks = $bookRepo->findBy(['status'=>'normal']);
        $this->assertCount(5,$findByBooks);
    }

    public function testAdminEntityManager(): void
    {

        $adminFactory = static::getContainer()->get(Factory::class);
        $this->assertInstanceOf(Factory::class,$adminFactory);
        $adminUser = $adminFactory->create('66485@qq.com','alexalex','alexmao',1000);

        $this->entityManager->persist($adminUser);

        $this->entityManager->flush();

        $adminRepo = static::getContainer()->get(AdminUserRepository::class);

        $this->assertInstanceOf(AdminUserRepository::class,$adminRepo);

        $findOneByUser = $adminRepo->findOneBy(['email'=>'66485@qq.com','password'=>'alexmao']);
        $this->assertSame($adminUser,$findOneByUser);
    }

    private function truncateEntities(array  $entities)
    {
        $connection = $this->entityManager->getConnection();
        $databasePlatform = $connection->getDatabasePlatform();
        if ($databasePlatform->supportsForeignKeyConstraints()){
            $connection->executeQuery('SET FOREIGN_KEY_CHECKS=0');
        }
        foreach ($entities as $entity){
            $query = $databasePlatform->getTruncateTableSQL(
                $this->entityManager->getClassMetadata($entity)->getTableName()
            );
            $connection->executeQuery($query);
        }
        if ($databasePlatform->supportsForeignKeyConstraints()){
            $connection->executeQuery('SET FOREIGN_KEY_CHECKS=1');
        }
    }
}
