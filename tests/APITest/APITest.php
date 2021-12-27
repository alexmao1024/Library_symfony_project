<?php

namespace App\Tests\APITest;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class APITest extends WebTestCase
{
    public function testLogin(): void
    {
        $client = static::createClient();
        $client->request('Post', '/login',
            [
                'email'=>'66485@qq.com',
                'password'=>'alexmao'
            ]);

        $this->assertResponseIsSuccessful();
    }

    public function testBookShow(): void
    {
        $client = static::createClient();
        $client->request('Post', '/showBook');

        $this->assertResponseIsSuccessful();
    }

    public function testCreateBook(): void
    {
        $client = static::createClient();
        $client->request('Post', '/create',
            [
                'author'=>'alex',
                'bookName'=>'alexMercer',
                'press'=>'alexx',
                'price'=>50,
                'quantity'=>10,
                'status'=>'normal'
            ]);

        $this->assertResponseIsSuccessful();
    }
}
