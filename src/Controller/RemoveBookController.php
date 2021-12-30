<?php

namespace App\Controller;

use App\Entity\Book;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RemoveBookController extends AbstractController
{
    /**
     * @Route("/removeBook/{id}", name="remove_book", methods={"DELETE"})
     */
    public function removeBook(EntityManagerInterface $entityManager,int $id): Response
    {

        $book = $entityManager->getRepository(Book::class)->find($id);
        if (!$book)
        {
            throw $this->createNotFoundException(
                'No book found for id'.$id
            );
        }
        $entityManager->remove($book);

        $entityManager->flush();

        return $this->json(['message'=>'Successfully'],200);

    }
}
