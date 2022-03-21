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
    private $faker;

    public function __construct(Factory $factory)
    {

        $this->factory = $factory;
        $this->faker = \Faker\Factory::create();
    }

    public function load(ObjectManager $manager): void
    {

        for($i=0;$i<20;$i++)
        {
            $book = $this->factory->createBook(
                $this->faker->isbn10(),
                $this->faker->name(),
                $this->faker->words(3,true),
                $this->faker->company(),
                $this->faker->numberBetween(0,500),
                $this->faker->numberBetween(0,50)
            );

            $manager->persist($book);
        }

        $manager->flush();
    }
}
