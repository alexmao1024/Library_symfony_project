<?php


namespace App\Factory;


use App\Entity\Book;
use App\Entity\Borrow;
use DateTimeInterface;

class BorrowFactory
{
    public function create(string $ISBN,string $bookName, DateTimeInterface $borrowAt, Book $book, string $status = 'borrowed'): Borrow
    {
        $borrow = new Borrow();
        $borrow->setISBN($ISBN);
        $borrow->setBookName($bookName);
        $borrow->setBorrowAt($borrowAt);
        $borrow->setBook($book);
        $borrow->setStatus($status);
        return $borrow;
    }

}