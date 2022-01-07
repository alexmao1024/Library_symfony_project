<?php


namespace App\Factory;


use App\Entity\Book;

class BookFactory
{

    public function create(string $ISBN,string $author,string $bookName,string $press,float $price,int $quantity = 1):Book
    {
        $book = new Book();
        $book->setISBN($ISBN);
        $book->setAuthor($author);
        $book->setBookName($bookName);
        $book->setPress($press);
        $book->setPrice($price);
        $book->setQuantity($quantity);
        return $book;
    }
}