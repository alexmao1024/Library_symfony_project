<?php

namespace App\Event;

use App\Entity\Book;

class AfterBookQuantityAddEvent
{

    private Book $book;
    private int $originalQuantity;

    public function __construct(Book $book, int $originalQuantity)
    {

        $this->book = $book;
        $this->originalQuantity = $originalQuantity;
    }

    /**
     * @return Book
     */
    public function getBook(): Book
    {
        return $this->book;
    }

    /**
     * @return int
     */
    public function getOriginalQuantity(): int
    {
        return $this->originalQuantity;
    }
}