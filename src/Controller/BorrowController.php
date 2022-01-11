<?php

namespace App\Controller;


use App\Entity\AdminUser;
use App\Entity\Book;
use App\Entity\Borrow;
use App\Entity\Message;
use App\Entity\NormalUser;
use App\Entity\Subscribe;
use App\Factory\Factory;
use App\Repository\BorrowRepository;
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
    public function borrow(Request $request, EntityManagerInterface $entityManager, Factory $factory): Response
    {
        $requestArray = $request->toArray();
        $ISBN = $requestArray['ISBN'];
        //用户id
        $normalUserId = $requestArray['id'];
        $borrowAt = DateTime::createFromFormat('Y-m-d', date('Y-m-d'));

        //查询用户实体管理器
        $normalUser = $entityManager->getRepository(NormalUser::class)->find($normalUserId);

        //查询书籍实体管理器
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

        $subBook = null;
        if ($normalUser->getSubscribe())
        {
            $subBook = $normalUser->getSubscribe()->getBook();
        }
        //如果该书预定记录数大于等于已有数量且不是预定者则不能外接
        $bookSubCount = count($book->getSubscribes(), 0);
        if ($bookSubCount >= $book->getQuantity() && !($subBook == $book))
        {
            throw $this->createAccessDeniedException(
                'The book has been subscribed!'
            );
        }
        elseif ($subBook == $book)
        {           //如果借的书就是该用户自己预定的书，则删除预定记录和消息通知
            $entityManager->remove($normalUser->getSubscribe());
            $subContent = $entityManager->getRepository(Message::class)->findOneBy(['normalUser' => $normalUser, 'content' => 'The book that you subscribed has returned!']);
            $entityManager->remove($subContent);
        }

        $book->setQuantity($book->getQuantity() - 1);

        $borrow = $factory->createBorrow($book->getISBN(),$book->getBookName(),$borrowAt,$normalUser);

        $entityManager->persist($borrow);

        $entityManager->flush();

        return $this->json([
                'id' => $borrow->getId(),
                'bookName' => $borrow->getBookName(),
                'borrowAt' => $borrow->getBorrowAt()->format('Y-m-d'),
                'status' => $borrow->getStatus(),
                'borrower' => $normalUser->getUsername()
            ]
        );
    }

    /**
     * @Route("/returnBook", name="return", methods={"POST"})
     */
    public function returnBook(Request $request,EntityManagerInterface $entityManager,Factory $factory): Response
    {
        $requestArray = $request->toArray();
        //借书记录ID
        $id = $requestArray['id'];

        //查询到该条借书记录
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
        //查询书籍对象
        $bookISBN = $borrow->getISBN();
        $book = $entityManager->getRepository(Book::class)->findOneBy(['ISBN'=>$bookISBN]);
        //如果图书记录已被删除则还书时重新插入新数据
        /*
        if (!$book)
        {
            $book = $factory->createBook($bookISBN,'author',$borrow->getBookName(),'press',0,0);
        }*/
        //查询管理员对象
        $admin = $entityManager->getRepository(AdminUser::class)->find(1);
        $spend = 0;

        //查询预定信息
        $subscribes = $entityManager->getRepository(Subscribe::class)->findBy(['book' => $book],['subscribeAt'=>'ASC']);

        //获取当前时间作为还书时间
        $returnAt = date('Y-m-d');
        $returnAtDate = DateTime::createFromFormat('Y-m-d', $returnAt);

        //插入还书需要的所有信息
        $borrow->setReturnAt($returnAtDate);
        $borrow->setStatus('Returned');
        $book->setQuantity($book->getQuantity()+1);
        //计算还书价格
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

        //如果有预定状态时的操作
        if ($subscribes)
        {
            for ($i=0;$i<count($subscribes,0);$i++)
            {
                if ($subscribes[$i]->getStatus() == 'noSent')
                {
                    $normalUser = $subscribes[$i]->getNormalUser();
                    $message = $factory->createMessage($normalUser, 'The book that you subscribed has returned!');
                    $subscribes[$i]->setStatus('sent');
                    $entityManager->persist($message);
                    break;
                }
            }

        }

        $entityManager->flush();

        return $this->json(
            [
                'spend'=>$spend,
                'balance'=>$admin->getBalance()+$spend
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
            $resultArray[$key]['id'] = $borrow->getId();
            $resultArray[$key]['ISBN'] = $borrow->getISBN();
            $resultArray[$key]['bookName'] = $borrow->getBookName();
            $resultArray[$key]['status'] = $borrow->getStatus();
            $resultArray[$key]['borrowAt'] = $borrow->getBorrowAt()->format('Y-m-d');
            $borrow->getReturnAt() ?
                $resultArray[$key]['returnAt'] = $borrow->getReturnAt()->format('Y-m-d')
                :$resultArray[$key]['returnAt'] = $borrow->getReturnAt();
            $resultArray[$key]['spend'] = $borrow->getSpend();
            $resultArray[$key]['borrower'] = $borrow->getBorrower()->getUsername();
        }

        return $response->setContent(json_encode($resultArray));
    }
}
