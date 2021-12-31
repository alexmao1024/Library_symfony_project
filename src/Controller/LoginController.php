<?php

namespace App\Controller;

use App\Repository\AdminUserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class LoginController extends AbstractController
{
    /**
     * @Route("/login", name="login", methods={"Post"})
     */
    public function login(Request $request,AdminUserRepository $adminUserRepository): Response
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
            ]
        );
    }

}
