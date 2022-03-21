<?php

namespace App\Controller;


use App\Entity\AdminUser;
use App\Entity\Book;
use App\Entity\Borrow;
use App\Entity\NormalUser;
use App\Entity\Subscribe;
use App\Event\AfterBookReturnEvent;
use App\Factory\Factory;
use App\Repository\BorrowRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Symfony\Component\Routing\Annotation\Route;
date_default_timezone_set('Asia/Shanghai');

class BorrowController extends AbstractController
{
    /**
     * @Route("/borrow", name="borrow", methods={"POST"})
     */
    public function borrow(Request $request, EntityManagerInterface $entityManager, Factory $factory,HubInterface $hub): Response
    {
        $requestArray = $request->toArray();
        $ISBN = $requestArray['ISBN'];
        //用户id
        $normalUserId = $requestArray['userId'];
        $borrowAt = DateTime::createFromFormat('Y-m-d H:i:s', date('Y-m-d H:i:s'));

        //查询用户实体管理器
        $normalUser = $entityManager->getRepository(NormalUser::class)->find($normalUserId);
        if (!$normalUser){
            throw new \Exception('No user found for: '.$normalUserId,401);
        }

        //查询书籍实体管理器
        $book = $entityManager->getRepository(Book::class)->findOneBy(['ISBN' => $ISBN]);
        if (!$book) {
            throw new \Exception('No book found for: '.$ISBN,404);
        }
        if ($book->getQuantity() - 1 < 0) {
            throw new \Exception('No book would be borrowed.',403);
        }

        $borrows = $entityManager->getRepository(Borrow::class)->findBy(['borrower' => $normalUser, 'status' => 'borrowed']);
        $borrowCount = 0;
        if ($borrows)
        {
            $borrowCount = count($borrows,0);
        }
        if ($borrowCount == 3)
        {
            throw new \Exception('Can\'t borrowed a book.Because you have been borrowed 3 books',400);
        }


        $userSub = null;
        $boolean = false;
        if ($book->getSubscribes()[0])
        {
            foreach ( $book->getSubscribes() as $subscribe)
            {
                if ($subscribe->getNormalUser() == $normalUser && $subscribe->getStatus() == 'sent')
                {
                    $boolean = true;
                    $userSub = $subscribe;
                    break;
                }
            }
        }

        //如果该书预定记录数大于等于已有数量且不是预定者或者没有被通知有库存则不能外接
        $bookSubCount = count($book->getSubscribes(), 0);
        if ($bookSubCount >= $book->getQuantity() && !($boolean))
        {
            throw new \Exception('Can\'t borrow',405);
        }
        elseif ($boolean)
        {           //如果借的书就是该用户自己预定的书，则删除预定记录
            $entityManager->remove($userSub);
        }

        $book->setQuantity($book->getQuantity() - 1);

        $borrow = $factory->createBorrow($book->getISBN(),$book->getBookName(),$borrowAt,$normalUser);

        $entityManager->persist($borrow);

        $entityManager->flush();

        $update = new Update(
            'https://library.com/books',
            json_encode([
                'type'=>'borrow',
                'ISBN'=>$book->getISBN(),
                'bookName'=>$book->getBookName(),
                'borrowId'=>$borrow->getId(),
                'status'=>$borrow->getStatus(),
                'borrowAt'=>$borrow->getBorrowAt()->format('Y-m-d H:i:s'),
                'userId'=>$borrow->getBorrower()->getId(),
                'quantity'=>$book->getQuantity()
            ])
        );

        $hub->publish($update);

        return $this->json([
                'borrowId' => $borrow->getId(),
                'bookName' => $borrow->getBookName(),
                'borrowAt' => $borrow->getBorrowAt()->format('Y-m-d H:i:s'),
                'status' => $borrow->getStatus(),
                'borrower' => $normalUser->getUsername()
            ]
        );
    }

    /**
     * @Route("/returnBook", name="return", methods={"POST"})
     */
    public function returnBook(Request $request,EntityManagerInterface $entityManager,
                               Factory $factory,HubInterface $hub,
                               EventDispatcherInterface $eventDispatcher): Response
    {
        $requestArray = $request->toArray();
        //借书记录ID
        $id = $requestArray['borrowId'];

        //查询到该条借书记录
        $borrow = $entityManager->getRepository(Borrow::class)->find($id);

        if (!$borrow)
        {
            throw new \Exception('No borrow found for: '.$id,404);
        }

        //查询书籍对象
        $bookISBN = $borrow->getISBN();
        $book = $entityManager->getRepository(Book::class)->findOneBy(['ISBN'=>$bookISBN]);

        //查询管理员对象
        $admin = $entityManager->getRepository(AdminUser::class)->findOneBy(['username'=>'alex']);
        $spend = 0;

        //查询预定信息
        $subscribes = $entityManager->getRepository(Subscribe::class)->findBy(['book' => $book],['subscribeAt'=>'ASC']);

        //获取当前时间作为还书时间
        $returnAt = date('Y-m-d H:i:s');
        $returnAtDate = DateTime::createFromFormat('Y-m-d H:i:s', $returnAt);

        //插入还书需要的所有信息
        $borrow->setReturnAt($returnAtDate);
        $borrow->setStatus('returned');
        $book->setQuantity($book->getQuantity()+1);
        //计算还书价格
        $interval = (int)$returnAtDate->diff($borrow->getBorrowAt())->format('%a');
        if ($interval<0)
        {
            throw new \Exception('Return time is fault!',400);
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

        $entityManager->flush();

        $event = new AfterBookReturnEvent($book);
        $eventDispatcher->dispatch($event);

        $updateBooks = new Update(
            'https://library.com/books',
            json_encode([
                'type'=>'return',
                'ISBN'=>$book->getISBN(),
                'quantity'=>$book->getQuantity(),
                'returnAt'=>$borrow->getReturnAt()->format('Y-m-d H:i:s'),
                'userId'=>$borrow->getBorrower()->getId(),
                'borrowId'=>$borrow->getId(),
                'spend'=>$borrow->getSpend()
            ])
        );



        $hub->publish($updateBooks);


        return $this->json(
            [
                'spend'=>$spend,
                'balance'=>$admin->getBalance()
            ]
        );
    }

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
            $resultArray[$key]['borrowId'] = $borrow->getId();
            $resultArray[$key]['ISBN'] = $borrow->getISBN();
            $resultArray[$key]['bookName'] = $borrow->getBookName();
            $resultArray[$key]['status'] = $borrow->getStatus();
            $resultArray[$key]['borrowAt'] = $borrow->getBorrowAt()->format('Y-m-d H:i:s');
            $borrow->getReturnAt() ?
                $resultArray[$key]['returnAt'] = $borrow->getReturnAt()->format('Y-m-d H:i:s') :
                $resultArray[$key]['returnAt'] = $borrow->getReturnAt();
            $resultArray[$key]['spend'] = $borrow->getSpend();
            $resultArray[$key]['borrower'] = $borrow->getBorrower()->getUsername();
        }

        return $response->setContent(json_encode($resultArray));
    }
}
