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
            throw $this->createAccessDeniedException(
                'Can\'t subscribe.'
            );
        }

        $subscribe = $factory->createSubscribe($book, $subscribeAt, $user);

        $entityManager->persist($subscribe);

        $entityManager->flush();

        return $this->json([],200);
    }

}
