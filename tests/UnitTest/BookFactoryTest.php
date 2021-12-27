<?php

namespace App\Tests;

use App\Entity\Book;
use PHPUnit\Framework\TestCase;
use App\Factory\BookFactory;

class BookFactoryTest extends TestCase
{
    public function testFactory(): void
    {
        $factory = new BookFactory();
        $book = $factory->create('刘慈欣','三体','科幻世界',100.0,5);

        $this->assertInstanceOf(Book::class,$book);
    }
}
