<?php

namespace App\Controller;

use App\Entity\Book;
use App\Factory\BookFactory;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CreateBookController extends AbstractController
{
    /**
     * @Route("/createBook", name="create_book",methods={"Post"})
     */
    public function createBook(Request $request,BookFactory $bookFactory,EntityManagerInterface $entityManager):Response
    {
        $author = $request->request->get('author');
        $bookName = $request->request->get('bookName');
        $press = $request->request->get('press');
        $price = $request->request->get('price');
        $quantity = $request->request->get('quantity');
        $status = $request->request->get('status');
        $book = $bookFactory->create($author,$bookName,$press,$price,$quantity,$status);

        $entityManager->persist($book);

        $entityManager->flush();
        return $this->json(['message'=>'Successfully,Book with id '.$book->getId()],200);
    }
}
