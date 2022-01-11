<?php

namespace App\DataFixtures;

use App\Factory\Factory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AdminUserFixtures extends Fixture
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

        $adminUser = $this->factory->createAdmin('694854836@qq.com','alex','alexmao');
        $manager->persist($adminUser);

        $manager->flush();
    }
}
