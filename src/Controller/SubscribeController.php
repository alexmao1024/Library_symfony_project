<?php

namespace App\Controller;

use App\Entity\Book;
use App\Entity\NormalUser;
use App\Entity\Subscribe;
use App\Factory\Factory;
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
        $userId = $requestArray['id'];
        $subscribeAt = DateTime::createFromFormat('Y-m-d H:i:s', date('Y-m-d H:i:s'));

        $book = $entityManager->getRepository(Book::class)->findOneBy(['ISBN' => $ISBN]);

        if (!$book)
        {
            throw $this->createNotFoundException(
                'This book does not exist.'
            );
        }
        $user = $entityManager->getRepository(NormalUser::class)->find($userId);
        if (!$user)
        {
            throw $this->createNotFoundException(
                'This user does not exist.'
            );
        }

        $subscribeExit = $entityManager->getRepository(Subscribe::class)->findOneBy(['normalUser' => $user]);

        if ($subscribeExit)
        {
            if ($subscribeExit->getBook() != $book)
            {
                throw $this->createAccessDeniedException(
                    'Can\'t subscribe.'
                );
            }
            if ($subscribeExit->getBook() == $book)
            {
                $entityManager->remove($subscribeExit);
                if ($subscribeExit->getNormalUser()->getMessages()[0])
                {
                    $entityManager->remove($subscribeExit->getNormalUser()->getMessages()[0]);
                }
                $entityManager->flush();
                return $this->json(['delete'=>'success'],200);
            }
        }

        $subscribe = $factory->createSubscribe($book, $subscribeAt, $user);

        $entityManager->persist($subscribe);

        $entityManager->flush();

        return $this->json(['add'=>'success'],200);
    }

}
