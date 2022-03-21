<?php

namespace App\Tests\UnitTest\APITest;

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

     public function testBorrow(): void
     {
         $client = static::createClient();
         $client->request('Post', '/borrow',
             [
                 'bookName'=>'三体1',
                 'borrowAt'=>'2021-12-30'
             ]);
         $request = $client->getRequest();
         $requestArray = $request->toArray();

         $response = $client->getResponse();


         $this->assertResponseIsSuccessful();
         $this->assertSame( '2021-12-30', $requestArray['borrowAt']);

     }

    public function testCreateBook(): void
    {
        $client = static::createClient();
        $client->request('Post', '/createBook',
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
