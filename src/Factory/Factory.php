<?php


namespace App\Factory;


use App\Entity\AdminUser;
use App\Entity\Book;
use App\Entity\Borrow;
use App\Entity\NormalUser;
use App\Entity\Subscribe;
use DateTimeInterface;

class Factory
{

    public function createAdmin(string $email,string $username,string $password,float $balance = 50000):AdminUser
    {
        $admin = new AdminUser();
        $admin->setEmail($email);
        $admin->setUsername($username);
        $admin->setPassword($password);
        $admin->setBalance($balance);

        return $admin;
    }

    public function createBook(string $ISBN,string $author,string $bookName,string $press,float $price,int $quantity = 1):Book
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

    public function createBorrow(string $ISBN,string $bookName, DateTimeInterface $borrowAt, NormalUser $normalUser,string $status = 'borrowed'): Borrow
    {
        $borrow = new Borrow();
        $borrow->setISBN($ISBN);
        $borrow->setBookName($bookName);
        $borrow->setBorrowAt($borrowAt);
        $borrow->setBorrower($normalUser);
        $borrow->setStatus($status);
        return $borrow;
    }

    public function createNormalUser(string $email,string $username,string $password): NormalUser
    {
        $normalUser = new NormalUser();
        $normalUser->setEmail($email);
        $normalUser->setUsername($username);
        $normalUser->setPassword($password);

        return $normalUser;
    }

    public function createSubscribe(Book $book, DateTimeInterface $subscribeAt, NormalUser $normalUser, string $status = 'noSent',DateTimeInterface $sentAt = null): Subscribe
    {
        $subscribe = new Subscribe();
        $subscribe->setBook($book);
        $subscribe->setSubscribeAt($subscribeAt);
        $subscribe->setNormalUser($normalUser);
        $subscribe->setStatus($status);
        $subscribe->setSentAt($sentAt);
        return $subscribe;
    }

}