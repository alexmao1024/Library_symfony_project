<?php

namespace App\Controller;

use App\Repository\BorrowRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ShowBorrowController extends AbstractController
{
    /**
     * @Route("/showBorrow", name="show_borrow", methods={"GET"})
     */
    public function showBorrow(BorrowRepository $borrowRepository): Response
    {
        $response = new Response();
        $borrows = $borrowRepository->findAll();

        if (!$borrows){
            return $this->json([]);
        }

        $resultArray = array();
        foreach ($borrows as $key => $borrow)
        {
            $resultArray[$key]['id'] = $borrow->getId();
            $resultArray[$key]['ISBN'] = $borrow->getISBN();
            $resultArray[$key]['bookName'] = $borrow->getBookName();
            $resultArray[$key]['status'] = $borrow->getStatus();
            $resultArray[$key]['borrowAt'] = $borrow->getBorrowAt()->format('Y-m-d');
            $borrow->getReturnAt() ?
                $resultArray[$key]['returnAt'] = $borrow->getReturnAt()->format('Y-m-d')
               :$resultArray[$key]['returnAt'] = $borrow->getReturnAt();
            $resultArray[$key]['spend'] = $borrow->getSpend();
        }

        return $response->setContent(json_encode($resultArray));
    }
}
