<?php

namespace App\DataFixtures;

use App\Factory\AdminUserFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AdminUserFixtures extends Fixture
{
    /**
     * @var AdminUserFactory
     */
    private $adminUserFactory;

    public function __construct(AdminUserFactory $adminUserFactory)
    {

        $this->adminUserFactory = $adminUserFactory;
    }

    public function load(ObjectManager $manager): void
    {

        $adminUser = $this->adminUserFactory->create('694854836@qq.com','alex','alexmao');
        $manager->persist($adminUser);

        $manager->flush();
    }
}
