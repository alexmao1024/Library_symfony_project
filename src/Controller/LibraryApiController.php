<?php

namespace App\Controller;

use App\Factory\BookFactory;
use App\Repository\AdminUserRepository;
use App\Repository\BookRepository;
use Doctrine\ORM\EntityManager;
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
        if ($adminUser){
            return $this->json(['admin_id'=>$adminUser->getId(),
                'email'=>$adminUser->getEmail(),
                'username'=>$adminUser->getUsername(),
                'password'=>$adminUser->getPassword(),
                'balance'=>$adminUser->getBalance()
            ]);
        }
        else
        {
            return $this->json(['message'=>'未找到匹配数据'],401);
        }
    }

    /**
     * @Route("/showBook", name="showBook", methods={"Post"})
     */
    public function showBook(Request $request,BookRepository $bookRepository): Response
    {
        $books = $bookRepository->findFilterStatus('normal');

        if ($books){
            return $this->json(['books'=>$books]);
        }
        else
        {
            return $this->json(['message'=>'暂时没有任何书籍']);
        }
    }

    /**
     * @Route("/create", name="createBook", methods={"Post"})
     */
    public function createBook(Request $request,BookFactory $bookFactory,EntityManager $entityManager):Response
    {
        $author = $request->request->get('author');
        $bookName = $request->request->get('bookName');
        $press = $request->request->get('press');
        $price = $request->request->get('price');
        $quantity = $request->request->get('quantity');
        $status = $request->request->get('status');
        $book = $bookFactory->create($author,$bookName,$press,$price,$quantity,$status);

        $entityManager->persist($book);

        $entityManager->flush();

        return $this->json(['message'=>'添加数据成功'],200);
    }

//    /**
//     * @Route("/update", name="update", methods={"Post"})
//     */
//    public function updateBook(Request $request,EntityManagerInterface $entityManager): Response
//    {
//        $entityManager->
//    }
}
