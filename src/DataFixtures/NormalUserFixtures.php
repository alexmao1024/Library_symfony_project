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

    public function __construct(Factory $factory)
    {

        $this->factory = $factory;
    }

    public function load(ObjectManager $manager): void
    {
        for ($i=0;$i<6;$i++)
        {
            $email = chr(rand(97,122)).rand(10001,99999).'@'.rand(101,199).'.com';
            $username = chr(rand(65,90)).chr(rand(97,122)).chr(rand(97,122));
            $normalUser = $this->factory->createNormalUser($email, $username, '' . rand(10001, 99999));
            $manager->persist($normalUser);
        }

        $manager->flush();
    }
}
