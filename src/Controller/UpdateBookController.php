<?php

namespace App\Controller;


use App\Entity\Book;
use App\Entity\Borrow;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UpdateBookController extends AbstractController
{
    /**
     * @Route("/updateBook", name="update_book", methods={"POST"})
     */
    public function updateBook(Request $request,EntityManagerInterface $entityManager): Response
    {
        $requestArray = $request->toArray();
        $ISBN = $requestArray['ISBN'];
        $author = $requestArray['author'];
        $bookName = $requestArray['bookName'];
        $press = $requestArray['press'];
        $price = $requestArray['price'];
        $quantity = $requestArray['quantity'];

        $book = $entityManager->getRepository(Book::class)->findOneBy(['ISBN'=>$ISBN]);
        if (!$book)
        {
            throw $this->createNotFoundException(
                'No book found for: '.$ISBN
            );
        }

        $borrows = $entityManager->getRepository(Borrow::class)->findBy(['ISBN' => $ISBN]);

        if ($bookName)
        {
            $book->setBookName($bookName);
            foreach ( $borrows as $borrow)
            {
                $borrow?->setBookName($bookName);
            }
        }
        if ($author)
        {
            $book->setAuthor($author);
        }
        if ($price)
        {
            $book->setPrice($price);
        }
        if ($press)
        {
            $book->setPress($press);
        }
        if ($quantity)
        {
            $book->setQuantity($quantity);
        }

        $entityManager->flush();

        return $this->json([],200);
    }
}
