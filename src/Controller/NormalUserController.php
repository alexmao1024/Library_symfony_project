<?php

namespace App\Controller;

use App\Factory\Factory;
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
            throw $this->createNotFoundException(
                'Passed an empty argument'
            );
        }

        $user = $factory->createNormalUser($email,$username,$password);

        $entityManager->persist($user);

        $entityManager->flush();

        return $this->json([
            'id'=>$user->getId(),
            'email'=>$user->getEmail(),
            'username'=>$user->getUsername()
        ]);
    }
}
