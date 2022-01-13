<?php

namespace App\Controller;


use App\Entity\Book;
use App\Entity\Borrow;
use App\Entity\NormalUser;
use App\Factory\Factory;
use App\Repository\BookRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BookController extends AbstractController
{
    /**
     * @Route("/showBook", name="show_book", methods={"GET"})
     */
    public function showBook(BookRepository $bookRepository): Response
    {
        $response = new Response();
        $books = $bookRepository->findAll();

        if (!$books) {
            return $this->json([]);
        }

        $resultArray = array();
        foreach ($books as $key => $book) {
            $resultArray[$key]['id'] = $book->getId();
            $resultArray[$key]['ISBN'] = $book->getISBN();
            $resultArray[$key]['bookName'] = $book->getBookName();
            $resultArray[$key]['author'] = $book->getAuthor();
            $resultArray[$key]['press'] = $book->getPress();
            $resultArray[$key]['price'] = $book->getPrice();
            $resultArray[$key]['quantity'] = $book->getQuantity();
        }

        return $response->setContent(json_encode($resultArray));
    }

    /**
     * @Route("/createBook", name="create_book",methods={"Post"})
     */
    public function createBook(Request $request,Factory $factory,EntityManagerInterface $entityManager):Response
    {
        $requestArray = $request->toArray();
        $ISBN = $requestArray['ISBN'];
        $author = $requestArray['author'];
        $bookName = $requestArray['bookName'];
        $press = $requestArray['press'];
        $price = $requestArray['price'];
        $quantity = $requestArray['quantity'];

        if (!$ISBN||!$author||!$bookName||!$press||!$price||!$quantity)
        {
            throw new \Exception('Passed an empty argument.',400);
        }

        $book = $factory->createBook($ISBN,$author,$bookName,$press,$price,$quantity);

        $entityManager->persist($book);

        $entityManager->flush();

        return $this->json([
            'id'=>$book->getId()
        ]);
    }

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

    /**
     * @Route("/updateBook", name="update_book", methods={"POST"})
     */
    public function updateBook(Request $request,EntityManagerInterface $entityManager,Factory $factory): Response
    {
        $requestArray = $request->toArray();
        $ISBN = $requestArray['ISBN'];
        $author = $requestArray['author'];
        $bookName = $requestArray['bookName'];
        $press = $requestArray['press'];
        $price = $requestArray['price'];
        $quantity = $requestArray['quantity'];

        $book = $entityManager->getRepository(Book::class)->findOneBy(['ISBN'=>$ISBN]);
        if (!$book)
        {
            throw $this->createNotFoundException(
                'No book found for: '.$ISBN
            );
        }

        $borrows = $entityManager->getRepository(Borrow::class)->findBy(['ISBN' => $ISBN]);

        if ($bookName)
        {
            $book->setBookName($bookName);
            foreach ( $borrows as $borrow)
            {
                $borrow?->setBookName($bookName);
            }
        }
        if ($author)
        {
            $book->setAuthor($author);
        }
        if ($price)
        {
            $book->setPrice($price);
        }
        if ($press)
        {
            $book->setPress($press);
        }
        if ($quantity)
        {
            if ($book->getQuantity()==0 && $book->getSubscribes())
            {
                $count = 0;
                for ($i=0;$i<count($book->getSubscribes(),0);$i++)
                {
                    if ($book->getSubscribes()[$i]->getStatus() == 'noSent')
                    {
                        $normalUser = $book->getSubscribes()[$i]->getNormalUser();
                        $message = $factory->createMessage($normalUser, '《'.$book->getSubscribes()[$i]->getBook()->getBookName().'》');
                        $book->getSubscribes()[$i]->setStatus('sent');
                        $sentAt = DateTime::createFromFormat('Y-m-d H:i:s', date('Y-m-d H:i:s'));
                        $book->getSubscribes()[$i]->setSentAt($sentAt);
                        $entityManager->persist($message);
                        $count ++;
                    }
                    if ($count >= $quantity)
                    {
                        break;
                    }
                }
            }
            $book->setQuantity($quantity);
        }

        $entityManager->flush();

        return $this->json([],200);
    }

    /**
     * @Route("/userShowBook/{userId}", name="user_show_book", methods={"GET"})
     */
    public function userShowBook(EntityManagerInterface $entityManager,int $userId): Response
    {

        $user = $entityManager->getRepository(NormalUser::class)->find($userId);
        if (!$user) {
            throw $this->createAccessDeniedException(
                'Access Denied.'
            );
        }

        $response = new Response();
        $books = $entityManager->getRepository(Book::class)->findAll();

        if (!$books) {
            return $this->json([]);
        }

        $resultArray = array();
        foreach ($books as $key => $book) {
            $resultArray[$key]['id'] = $book->getId();
            $resultArray[$key]['ISBN'] = $book->getISBN();
            $resultArray[$key]['bookName'] = $book->getBookName();
            $resultArray[$key]['author'] = $book->getAuthor();
            $resultArray[$key]['press'] = $book->getPress();
            $resultArray[$key]['price'] = $book->getPrice();
            $resultArray[$key]['quantity'] = $book->getQuantity();
            $resultArray[$key]['status'] = null;

            if (($user->getSubscribe() ? $user->getSubscribe()->getBook() : null) == $book )
            {
                $resultArray[$key]['status'] = '已预订';
            }
            elseif ($book->getQuantity() == 0)
            {
                $borrows = $entityManager->getRepository(Borrow::class)->findBy(['borrower' => $user]);
                if ($borrows)
                {
                    foreach ( $borrows as $borrow )
                    {
                        if ($borrow->getISBN() == $book->getISBN())
                        {
                            $resultArray[$key]['status'] = '已借阅';
                            break;
                        }
                        else
                        {
                            $resultArray[$key]['status'] = '可预订';
                        }
                    }
                }

            }
        }

        return $response->setContent(json_encode($resultArray));
    }
}
