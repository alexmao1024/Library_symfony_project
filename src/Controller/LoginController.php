<?php

namespace App\Controller;

use App\Repository\AdminUserRepository;
use App\Repository\NormalUserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class LoginController extends AbstractController
{
    /**
     * @Route("/adminLogin", name="adminLogin", methods={"Post"})
     */
    public function adminLogin(Request $request,AdminUserRepository $adminUserRepository): Response
    {

        $requestArray = $request->toArray();
        $email = $requestArray['email'];
        $password = $requestArray['password'];

        $adminUser = $adminUserRepository->findOneBy(['email' => $email, 'password' => $password]);
        if (!$adminUser){
            throw $this->createNotFoundException(
                'Login in failed'
            );
        }

        return $this->json(
            [
                'id'=>$adminUser->getId(),
                'username'=>$adminUser->getUsername(),
                'balance'=>$adminUser->getBalance()
            ]
        );
    }

    /**
     * @Route("/userLogin", name="userLogin", methods={"Post"})
     */
    public function userLogin(Request $request,NormalUserRepository $normalUserRepository): Response
    {

        $requestArray = $request->toArray();
        $email = $requestArray['email'];
        $password = $requestArray['password'];

        $normalUser = $normalUserRepository->findOneBy(['email' => $email, 'password' => $password]);
        if (!$normalUser){
            throw $this->createNotFoundException(
                'Login in failed'
            );
        }

        return $this->json(
            [
                'id'=>$normalUser->getId(),
                'username'=>$normalUser->getUsername()
            ]
        );
    }

}
