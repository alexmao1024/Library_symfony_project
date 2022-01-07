<?php

namespace App\Controller;


use App\Entity\Book;
use App\Factory\BorrowFactory;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BorrowController extends AbstractController
{
    /**
     * @Route("/borrow", name="borrow", methods={"POST"})
     */
    public function borrow(Request $request, EntityManagerInterface $entityManager, BorrowFactory $borrowFactory): Response
    {
        $requestArray = $request->toArray();
        $ISBN = $requestArray['ISBN'];
        $borrowAt = DateTime::createFromFormat('Y-m-d', date('Y-m-d'));


        $book = $entityManager->getRepository(Book::class)->findOneBy(['ISBN' => $ISBN]);
        if (!$book) {
            throw $this->createNotFoundException(
                'No book found : ' . $ISBN
            );
        }
        if ($book->getQuantity() - 1 < 0) {
            throw $this->createAccessDeniedException(
                'No book would be borrowed.'
            );
        }
        $book->setQuantity($book->getQuantity() - 1);

        $borrow = $borrowFactory->create($book->getISBN(),$book->getBookName(), $borrowAt);

        $entityManager->persist($borrow);

        $entityManager->flush();

        return $this->json([
                'id' => $borrow->getId(),
                'bookName' => $borrow->getBookName(),
                'borrowAt' => $borrow->getBorrowAt()->format('Y-m-d'),
                'status' => $borrow->getStatus()
            ]
        );
    }
}
