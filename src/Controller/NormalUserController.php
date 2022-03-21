<?php

namespace App\Controller;

use App\Entity\NormalUser;
use App\Factory\Factory;
use App\Repository\NormalUserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class NormalUserController extends AbstractController
{
    /**
     * @Route("/register", name="register",methods={"Post"})
     */
    public function register(Request $request,Factory $factory,EntityManagerInterface $entityManager):Response
    {
        $requestArray = $request->toArray();
        $email = $requestArray['email'];
        $username = $requestArray['username'];
        $password = $requestArray['password'];

        if (!$email||!$username||!$password)
        {
            throw new \Exception('Passed an empty argument.',400);
        }

        $user = $factory->createNormalUser($email,$username,$password);

        $entityManager->persist($user);

        $entityManager->flush();

        return $this->json([
            'userId'=>$user->getId(),
            'email'=>$user->getEmail(),
            'username'=>$user->getUsername()
        ]);
    }

    /**
     * @Route("/removeUser/{id}", name="remove_user", methods={"DELETE"})
     */
    public function removeUser(EntityManagerInterface $entityManager,int $id): Response
    {

        $user = $entityManager->getRepository(NormalUser::class)->find($id);
        if (!$user)
        {
            throw new \Exception('No user found for: '.$id,404);
        }
        if ($user->getSubscribes()[0])
        {
            throw new \Exception('Can\'t remove.Because he subscribed book.',403);
        }
        $borrows = $user->getBorrows();
        foreach ( $borrows as $borrow )
        {
            if ($borrow->getStatus() == 'borrowed')
            {
                throw new \Exception('Can\'t remove.Because he is borrowing book.',403);
            }
        }

        $entityManager->remove($user);

        $entityManager->flush();

        return $this->json([],200);

    }

    /**
     * @Route("/updateUser", name="update_user", methods={"POST"})
     */
    public function updateUser(Request $request,EntityManagerInterface $entityManager): Response
    {
        $requestArray = $request->toArray();
        $email = $requestArray['email'];
        $username = $requestArray['username'];
        $password = $requestArray['password'];
        $id = $requestArray['userId'];


        $user = $entityManager->getRepository(NormalUser::class)->find($id);
        if (!$user)
        {
            throw new \Exception('No user found for: '.$id,404);
        }

        if ($email)
        {
            $user->setEmail($email);
        }
        if ($username)
        {
            $user->setUsername($username);
        }
        if ($password)
        {
            $user->setPassword($password);
        }

        $entityManager->flush();

        return $this->json([],200);
    }

    /**
     * @Route("/showUser", name="show_user", methods={"GET"})
     */
    public function showUser(NormalUserRepository $normalUserRepository): Response
    {
        $response = new Response();
        $users = $normalUserRepository->findAll();

        if (!$users) {
            return $this->json([]);
        }

        $resultArray = array();
        foreach ($users as $key => $user) {
            $resultArray[$key]['userId'] = $user->getId();
            $resultArray[$key]['email'] = $user->getEmail();
            $resultArray[$key]['username'] = $user->getUsername();
            $resultArray[$key]['password'] = $user->getPassword();
        }

        return $response->setContent(json_encode($resultArray));
    }

    /**
     * @Route("/showOwnRecords/{userId}", name="show_own_records", methods={"GET"})
     */
    public function showOwnRecords(int $userId,EntityManagerInterface $entityManager): Response
    {
        $response = new Response();
        $user = $entityManager->getRepository(NormalUser::class)->find($userId);
        if (!$user) {
            throw new \Exception('Access Denied.',403);
        }

        $borrows = $user->getBorrows();
        if (!$borrows)
        {
            return $this->json([]);
        }

        $resultArray = array();
        foreach ($borrows as $key => $borrow) {
            $resultArray[$key]['borrowId'] = $borrow->getId();
            $resultArray[$key]['ISBN'] = $borrow->getISBN();
            $resultArray[$key]['bookName'] = $borrow->getBookName();
            $resultArray[$key]['status'] = $borrow->getStatus();
            $resultArray[$key]['borrowAt'] = $borrow->getBorrowAt()->format('Y-m-d H:i:s');
            $borrow->getReturnAt() ?
                $resultArray[$key]['returnAt'] = $borrow->getReturnAt()->format('Y-m-d H:i:s')
                :$resultArray[$key]['returnAt'] = $borrow->getReturnAt();
            $resultArray[$key]['spend'] = $borrow->getSpend();
        }

        return $response->setContent(json_encode($resultArray));
    }
}
