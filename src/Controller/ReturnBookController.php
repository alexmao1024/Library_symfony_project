<?php

namespace App\Controller;

use App\Entity\AdminUser;
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
        if ($borrow->getReturnAt())
        {
            throw $this->createAccessDeniedException(
                'The book has already returned.'
            );
        }

        $bookId = $borrow->getBook()->getId();
        $book = $entityManager->getRepository(Book::class)->find($bookId);
        $admin = $entityManager->getRepository(AdminUser::class)->find(1);
        $spend = 0;

        if ($returnAt)
        {
            $returnAtDate = DateTime::createFromFormat('Y-m-d', $returnAt);
            $borrow->setReturnAt($returnAtDate);
            $borrow->setStatus('Returned');
            $book->setQuantity($book->getQuantity()+1);
            $interval = (int)$returnAtDate->diff($borrow->getBorrowAt())->format('%a');
            if ($interval<0)
            {
                throw $this->createAccessDeniedException(
                    'Return time is fault!'
                );
            }
            elseif ($interval<=14)
            {
                $borrow->setSpend(0);
            }
            else
            {
                $spend = $interval - 14;
                $borrow->setSpend($spend);
                $admin->setBalance($admin->getBalance()+$spend);
            }
        }

        $entityManager->flush();

        return $this->json(
            [
                'message'=>'Returned Successfully!',
                'balance'=>$admin->getBalance()+$spend
            ]
        );
    }
}
