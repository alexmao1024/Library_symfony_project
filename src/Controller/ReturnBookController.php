<?php

namespace App\Controller;

use App\Entity\Book;
use App\Entity\Borrow;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ReturnBookController extends AbstractController
{
    /**
     * @Route("/returnBook", name="return", methods={"POST"})
     */
    public function returnBook(Request $request,EntityManagerInterface $entityManager): Response
    {
        $requestArray = $request->toArray();
        $id = $requestArray['id'];
        $returnAt = $requestArray['returnAt'];

        $borrow = $entityManager->getRepository(Borrow::class)->find($id);

        if (!$borrow)
        {
            throw $this->createNotFoundException(
                'No borrowing record found for id'.$id
            );
        }

        $bookId = $borrow->getBook()->getId();
        $book = $entityManager->getRepository(Book::class)->find($bookId);

        if ($returnAt)
        {
            $borrow->setReturnAt(DateTime::createFromFormat('Y-m-d', $returnAt));
            $borrow->setStatus('Returned');
            $book->setQuantity($book->getQuantity()+1);
        }

        $entityManager->flush();

        return $this->json(
            [
                'message'=>'Returned Successfully!'
            ]
        );
    }
}
