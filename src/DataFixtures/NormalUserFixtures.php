<?php

namespace App\DataFixtures;

use App\Factory\Factory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class NormalUserFixtures extends Fixture
{
    /**
     * @var Factory
     */
    private $factory;
    private $faker;

    public function __construct(Factory $factory)
    {
        $this->faker = \Faker\Factory::create();
        $this->factory = $factory;
    }

    public function load(ObjectManager $manager): void
    {
        for ($i=0;$i<6;$i++)
        {
            $normalUser = $this->factory->createNormalUser(
                $this->faker->email(),
                $this->faker->userName(),
                $this->faker->password(6,10)
            );
            $manager->persist($normalUser);
        }

        $manager->flush();
    }
}
