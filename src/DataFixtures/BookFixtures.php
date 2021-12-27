<?php

namespace App\DataFixtures;

use App\Factory\BookFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class BookFixtures extends Fixture
{

    /**
     * @var BookFactory
     */
    private $bookFactory;

    public function __construct(BookFactory $bookFactory)
    {

        $this->bookFactory = $bookFactory;
    }

    public function load(ObjectManager $manager): void
    {

        for($i=0;$i<5;$i++)
        {
            $book = $this->bookFactory->create('刘慈欣', '三体' . $i, '科幻世界', 100.0 + $i, $i);

            $manager->persist($book);
        }

        $manager->flush();
    }
}
