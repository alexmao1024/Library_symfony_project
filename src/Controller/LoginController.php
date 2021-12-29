<?php

namespace App\Controller;

use App\Repository\AdminUserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LibraryApiController extends AbstractController
{
    /**
     * @Route("/login", name="login", methods={"Post"})
     */
    public function login(Request $request,AdminUserRepository $adminUserRepository): Response
    {
        $email = $request->request->get('email');
        $password = $request->request->get('password');

        $adminUser = $adminUserRepository->findOneBy(['email' => $email, 'password' => $password]);
        if (!$adminUser){
            throw $this->createNotFoundException(
                'Login in failed'
            );
        }
        return $this->json(['admin_id'=>$adminUser->getId(),
            'email'=>$adminUser->getEmail(),
            'username'=>$adminUser->getUsername(),
            'password'=>$adminUser->getPassword(),
            'balance'=>$adminUser->getBalance()
        ]);
    }

}
