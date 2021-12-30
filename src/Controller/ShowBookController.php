<?php

namespace App\Controller;

use App\Repository\BookRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ShowBookController extends AbstractController
{
    /**
     * @Route("/showBook", name="show_book", methods={"GET"})
     */
    public function showBook(BookRepository $bookRepository): Response
    {
        $response = new Response();
        $books = $bookRepository->findAll();

        if (!$books){
            throw $this->createNotFoundException(
                'There are no books at the moment'
            );
        }

        $resultArray = array();
        foreach ($books as $key => $book)
        {
            $resultArray[$key]['id'] = $book->getId();
            $resultArray[$key]['bookName'] = $book->getBookName();
            $resultArray[$key]['author'] = $book->getAuthor();
            $resultArray[$key]['press'] = $book->getPress();
            $resultArray[$key]['price'] = $book->getPrice();
            $resultArray[$key]['quantity'] = $book->getQuantity();
        }

        return $response->setContent(json_encode($resultArray));
    }
}
