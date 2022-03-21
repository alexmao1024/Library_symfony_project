<?php

namespace App\Controller;

use App\Entity\Book;
use App\Entity\NormalUser;
use App\Factory\Factory;
use App\Repository\SubscribeRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
date_default_timezone_set('Asia/Shanghai');

class SubscribeController extends AbstractController
{
    /**
     * @Route("/subscribe", name="subscribe", methods={"POST"})
     */
    public function subscribe(Request $request, EntityManagerInterface $entityManager, Factory $factory): Response
    {
        $requestArray = $request->toArray();
        $ISBN = $requestArray['ISBN'];
        $userId = $requestArray['userId'];
        $subscribeAt = DateTime::createFromFormat('Y-m-d H:i:s', date('Y-m-d H:i:s'));

        $book = $entityManager->getRepository(Book::class)->findOneBy(['ISBN' => $ISBN]);

        if (!$book)
        {
            throw new \Exception('This book does not exist.',404);
        }
        $user = $entityManager->getRepository(NormalUser::class)->find($userId);
        if (!$user)
        {
            throw new \Exception('This user does not exist.',401);
        }

        $subscribeExit = $user->getSubscribes();

        if ($subscribeExit)
        {

            $boolean = false;
            $subRemo = null;
            foreach ( $subscribeExit as $sub)
            {
                if ($sub->getBook() == $book)
                {
                    $boolean = true;
                    $subRemo = $sub;
                    break;
                }
            }

            if ($boolean)
            {
                $entityManager->remove($subRemo);
                $entityManager->flush();
                return $this->json(['delete'=>'success'],200);
            }

            $subscribeExitCount = count($subscribeExit,0);
            if ($subscribeExitCount == 3)
            {
                throw new \Exception('Can\'t subscribe.',403);
            }

        }

        if ($book->getSubscribes())
        {
            $subscribesCount = count($book->getSubscribes(),0);
            if ($book->getQuantity()>$subscribesCount)
            {
                $subscribe = $factory->createSubscribe($book, $subscribeAt, $user,'sent',$subscribeAt);
            }
            else
            {
                $subscribe = $factory->createSubscribe($book, $subscribeAt, $user);
            }
            $entityManager->persist($subscribe);
        }


        $entityManager->flush();

        return $this->json(['add'=>'success'],200);
    }

    /**
     * @Route("/showSubscribe", name="show_subscribe", methods={"GET"})
     */
    public function showSubscribe(SubscribeRepository $subscribeRepository): Response
    {
        $response = new Response();
        $subscribes = $subscribeRepository->findAll();

        if (!$subscribes){
            return $this->json([]);
        }

        $resultArray = array();
        foreach ($subscribes as $key => $subscribe)
        {
            $resultArray[$key]['subscribeId'] = $subscribe->getId();
            $resultArray[$key]['ISBN'] = $subscribe->getBook()->getISBN();
            $resultArray[$key]['bookName'] = $subscribe->getBook()->getBookName();
            $resultArray[$key]['author'] = $subscribe->getBook()->getAuthor();
            $resultArray[$key]['userId'] = $subscribe->getNormalUser()->getId();
            $resultArray[$key]['username'] = $subscribe->getNormalUser()->getUsername();
            $resultArray[$key]['subscribeAt'] = $subscribe->getSubscribeAt()->format('Y-m-d H:i:s');
            $resultArray[$key]['status'] = $subscribe->getStatus();
            $subscribe->getSentAt() ?
                $resultArray[$key]['sentAt'] = $subscribe->getSentAt()->format('Y-m-d H:i:s') :
                $resultArray[$key]['sentAt'] = $subscribe->getSentAt();
        }

        return $response->setContent(json_encode($resultArray));
    }

    /**
     * @Route("/showUserSubscribe/{userId}", name="show_user_subscribe", methods={"GET"})
     */
    public function showUserSubscribe(EntityManagerInterface $entityManager,int $userId): Response
    {
        $response = new Response();

        $user = $entityManager->getRepository(NormalUser::class)->find($userId);
        if (!$user)
        {
            throw new \Exception('Access Denied.',403);
        }
        $subscribes = $user->getSubscribes();

        if (!$subscribes){
            return $this->json([]);
        }

        $resultArray = array();
        foreach ($subscribes as $key => $subscribe)
        {
            $resultArray[$key]['subscribeId'] = $subscribe->getId();
            $resultArray[$key]['ISBN'] = $subscribe->getBook()->getISBN();
            $resultArray[$key]['bookName'] = $subscribe->getBook()->getBookName();
            $resultArray[$key]['author'] = $subscribe->getBook()->getAuthor();
            $resultArray[$key]['subscribeAt'] = $subscribe->getSubscribeAt()->format('Y-m-d H:i:s');
            $resultArray[$key]['status'] = $subscribe->getStatus();
        }

        return $response->setContent(json_encode($resultArray));
    }
}
