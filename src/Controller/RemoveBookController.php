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
     * @Route("/removeBook/{ISBN}", name="remove_book", methods={"DELETE"})
     */
    public function removeBook(EntityManagerInterface $entityManager,string $ISBN): Response
    {

        $book = $entityManager->getRepository(Book::class)->findOneBy(['ISBN' => $ISBN]);
        if (!$book)
        {
            throw $this->createNotFoundException(
                'No book found for: '.$ISBN
            );
        }

        $entityManager->remove($book);

        $entityManager->flush();

        return $this->json([],200);

    }
}
