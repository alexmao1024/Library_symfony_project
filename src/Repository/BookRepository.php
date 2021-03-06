<?php

namespace App\Repository;

use App\Entity\Book;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Book|null find($id, $lockMode = null, $lockVersion = null)
 * @method Book|null findOneBy(array $criteria, array $orderBy = null)
 * @method Book[]    findAll()
 * @method Book[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BookRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Book::class);
    }

//     /**
//      * @return Book[] Returns an array of Book objects
//      */
//    public function findFilterQuantity(int $value): array
//    {
//        $entityManager = $this->getEntityManager();
//
//        $query = $entityManager->createQuery(
//            'SELECT book
//                 FROM APP\Entity\Book book,APP\Entity\Subscribe subscribe
//                 WHERE book = subscribe.book AND
//                 '
//        )->setParameter();
//        return
//    }


    public function findAllBy(string $value): Book
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.bookName = :value')
            ->setParameter('value', $value)
            ->getQuery()
            ->getResult()
        ;
    }

}
