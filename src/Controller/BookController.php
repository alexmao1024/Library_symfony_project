<?php

namespace App\Controller;


use App\Entity\Book;
use App\Entity\Borrow;
use App\Entity\NormalUser;
use App\Event\AfterBookQuantityAddEvent;
use App\Factory\Factory;
use App\Repository\BookRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
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
            $resultArray[$key]['bookId'] = $book->getId();
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
    public function createBook(Request $request,Factory $factory,EntityManagerInterface $entityManager,HubInterface $hub):Response
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

        $update = new Update(
            'https://library.com/books',
            json_encode([
                'type'=>'create',
                'ISBN'=>$ISBN,
                'author'=>$author,
                'bookName'=>$bookName,
                'press'=>$press,
                'price'=>$price,
                'quantity'=>$quantity
            ])
        );

        $hub->publish($update);

        return $this->json([
            'book_id'=>$book->getId()
        ]);
    }

    /**
     * @Route("/removeBook/{ISBN}", name="remove_book", methods={"DELETE"})
     */
    public function removeBook(EntityManagerInterface $entityManager,string $ISBN,HubInterface $hub): Response
    {

        $book = $entityManager->getRepository(Book::class)->findOneBy(['ISBN' => $ISBN]);
        if (!$book)
        {
            throw new \Exception('No book found for: '.$ISBN,404);
        }
        $borrows = $entityManager->getRepository(Borrow::class)->findBy(['ISBN' => $ISBN]);
        foreach ( $borrows as $borrow)
        {
            if ($borrow->getStatus() == 'borrowed')
            {
                throw new \Exception('Can\'t remove.',403);
            }
        }


        $entityManager->remove($book);

        $entityManager->flush();

        $update = new Update(
            'https://library.com/books',
            json_encode([
                'type'=>'remove',
                'ISBN'=>$ISBN
            ])
        );

        $hub->publish($update);

        return $this->json([],200);

    }

    /**
     * @Route("/updateBook", name="update_book", methods={"POST"})
     */
    public function updateBook(Request $request,EntityManagerInterface $entityManager,
                               Factory $factory,HubInterface $hub,
                               EventDispatcherInterface $eventDispatcher): Response
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
            throw new \Exception('No book found for: '.$ISBN,404);
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

        $originalQuantity = $book->getQuantity();
        if (isset($quantity))
        {
            $book->setQuantity($quantity);
        }

        $entityManager->flush();

        $event = new AfterBookQuantityAddEvent($book,$originalQuantity);
        $eventDispatcher->dispatch($event);

        $update = new Update(
            'https://library.com/books',
            json_encode([
                'type'=>'update',
                'ISBN'=>$book->getISBN(),
                'author'=>$book->getAuthor(),
                'bookName'=>$book->getBookName(),
                'press'=>$book->getPress(),
                'price'=>$book->getPrice(),
                'quantity'=>$book->getQuantity()
            ])
        );

        $hub->publish($update);

        return $this->json([],200);
    }

    /**
     * @Route("/userShowBook/{userId}", name="user_show_book", methods={"GET"})
     */
    public function userShowBook(EntityManagerInterface $entityManager,int $userId): Response
    {

        $user = $entityManager->getRepository(NormalUser::class)->find($userId);
        if (!$user) {
            throw new \Exception('Access Denied.',403);
        }

        $response = new Response();
        $books = $entityManager->getRepository(Book::class)->findAll();

        if (!$books) {
            return $this->json([]);
        }

        $resultArray = array();
        foreach ($books as $key => $book) {
            $resultArray[$key]['bookId'] = $book->getId();
            $resultArray[$key]['ISBN'] = $book->getISBN();
            $resultArray[$key]['bookName'] = $book->getBookName();
            $resultArray[$key]['author'] = $book->getAuthor();
            $resultArray[$key]['press'] = $book->getPress();
            $resultArray[$key]['price'] = $book->getPrice();
            $resultArray[$key]['quantity'] = $book->getQuantity();
            $resultArray[$key]['status'] = '0';

            $boolean = false;
            if ($user->getSubscribes()[0])
            {
                foreach ($user->getSubscribes() as $subscribe)
                {
                    if ($subscribe->getBook() == $book)
                    {
                        $boolean = true;
                        break;
                    }
                }
            }
            if ($boolean)
            {
                //设置状态已预定
                $book->setStatus('1');
                $resultArray[$key]['status'] = $book->getStatus();
            }
        }

        return $response->setContent(json_encode($resultArray));
    }
}
