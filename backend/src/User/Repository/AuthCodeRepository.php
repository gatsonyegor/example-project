<?php

declare(strict_types=1);

namespace App\User\Repository;

use App\User\Entity\AuthCode;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AuthCode>
 */
class AuthCodeRepository extends ServiceEntityRepository
{
	public function __construct(ManagerRegistry $registry)
	{
		parent::__construct($registry, AuthCode::class);
	}

	//    /**
	//     * @return AuthCodes[] Returns an array of AuthCodes objects
	//     */
	//    public function findByExampleField($value): array
	//    {
	//        return $this->createQueryBuilder('a')
	//            ->andWhere('a.exampleField = :val')
	//            ->setParameter('val', $value)
	//            ->orderBy('a.id', 'ASC')
	//            ->setMaxResults(10)
	//            ->getQuery()
	//            ->getResult()
	//        ;
	//    }

	//    public function findOneBySomeField($value): ?AuthCodes
	//    {
	//        return $this->createQueryBuilder('a')
	//            ->andWhere('a.exampleField = :val')
	//            ->setParameter('val', $value)
	//            ->getQuery()
	//            ->getOneOrNullResult()
	//        ;
	//    }
}
