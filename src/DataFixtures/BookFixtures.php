<?php

namespace App\DataFixtures;

use App\Factory\Factory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class BookFixtures extends Fixture
{

    /**
     * @var Factory
     */
    private $factory;

    public function __construct(Factory $factory)
    {

        $this->factory = $factory;
    }

    public function load(ObjectManager $manager): void
    {

        for($i=0;$i<5;$i++)
        {
            $book = $this->factory->createBook(rand(100000,500000).'','刘慈欣', '三体' . $i, '科幻世界', 100.0 + $i, $i);

            $manager->persist($book);
        }

        $manager->flush();
    }
}
