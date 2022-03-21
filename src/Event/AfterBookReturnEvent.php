<?php

namespace App\Event;

use App\Entity\Book;

class AfterBookReturnEvent
{
    private Book $book;

    public function __construct(Book $book)
    {
        $this->book = $book;
    }

    /**
     * @return Book
     */
    public function getBook(): Book
    {
        return $this->book;
    }
}