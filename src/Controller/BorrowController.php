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
        $bookName = $requestArray['bookName'];
        $borrowAt = DateTime::createFromFormat('Y-m-d', $requestArray['borrowAt']);


        $book = $entityManager->getRepository(Book::class)->findOneBy(['bookName' => $bookName]);
        if (!$book) {
            throw $this->createNotFoundException(
                'No book found : ' . $bookName
            );
        }
        if ($book->getQuantity() - 1 < 0) {
            throw $this->createAccessDeniedException(
                'No book would be borrowed.'
            );
        }
        $book->setQuantity($book->getQuantity() - 1);

        $borrow = $borrowFactory->create($book->getBookName(), $borrowAt, $book);

        $entityManager->persist($borrow);

        $entityManager->flush();

        return $this->json([
                'message' => 'Successfully,Book with id ' . $book->getId(),
                'id:' => $borrow->getId()
            ]
        );
    }
}
