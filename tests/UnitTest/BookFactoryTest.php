<?php

namespace App\Tests\UnitTest;

use App\Entity\Book;
use PHPUnit\Framework\TestCase;
use App\Factory\Factory;

class BookFactoryTest extends TestCase
{
    public function testFactory(): void
    {
        $factory = new Factory();
        $book = $factory->createBook('刘慈欣','三体','科幻世界',100.0,5);

        $this->assertInstanceOf(Book::class,$book);
    }
}
