<?php


namespace App\Factory;


use App\Entity\Borrow;
use DateTimeInterface;

class BorrowFactory
{
    public function create(string $ISBN,string $bookName, DateTimeInterface $borrowAt, string $status = 'borrowed'): Borrow
    {
        $borrow = new Borrow();
        $borrow->setISBN($ISBN);
        $borrow->setBookName($bookName);
        $borrow->setBorrowAt($borrowAt);
        $borrow->setStatus($status);
        return $borrow;
    }

}