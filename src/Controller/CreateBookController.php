<?php

namespace App\Controller;


use App\Factory\BookFactory;
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
        $requestArray = $request->toArray();
        $author = $requestArray['author'];
        $bookName = $requestArray['bookName'];
        $press = $requestArray['press'];
        $price = $requestArray['price'];
        $quantity = $requestArray['quantity'];

        if (!$author||!$bookName||!$press||!$price||!$quantity)
        {
            throw $this->createNotFoundException(
                'Passed an empty argument'
            );
        }

        $book = $bookFactory->create($author,$bookName,$press,$price,$quantity);

        $entityManager->persist($book);

        $entityManager->flush();

        return $this->json([
            'message'=>'Successfully,Book with id '.$book->getId(),
            'id:'=>$book->getId()
        ],200);
    }
}
