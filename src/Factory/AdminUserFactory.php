<?php


namespace App\Factory;


use App\Entity\AdminUser;

class AdminUserFactory
{

    public function create(string $email,string $username,string $password,float $balance = 50000):AdminUser
    {
        $admin = new AdminUser();
        $admin->setEmail($email);
        $admin->setUsername($username);
        $admin->setPassword($password);
        $admin->setBalance($balance);

        return $admin;
    }
}