<?php

declare(strict_types=1);

namespace App\News\Repository;

use App\News\Entity\News;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<News>
 */
class NewsRepository extends ServiceEntityRepository
{
	public function __construct(ManagerRegistry $registry)
	{
		parent::__construct($registry, News::class);
	}

	/**
	 * @return News[]
	 */
	public function getRecentNews($limit = 0, $offset = 0): array
	{
		return $this->createQueryBuilder('n')
			->addOrderBy('n.createdAt', 'desc')
			->setMaxResults($limit)
			->setFirstResult($offset)
			->getQuery()
			->getResult()
		;
	}

	/**
	 * @return News[]
	 */
	public function findBySearch($search, $limit = 0, $offset = 0): array
	{
		return $this->createQueryBuilder('n')
			->orWhere('n.content LIKE :search')
			->orWhere('n.title LIKE :search')
			->setParameter('search', '%'.$search.'%')
			->addOrderBy('n.createdAt', 'desc')
			->setMaxResults($limit)
			->setFirstResult($offset)
			->getQuery()
			->getResult()
		;
	}

	public function countBySearch(string $search): int
	{
		return (int) $this->createQueryBuilder('n')
			->select('COUNT(n.id)')
			->orWhere('n.content LIKE :search')
			->orWhere('n.title LIKE :search')
			->setParameter('search', '%'.$search.'%')
			->getQuery()
			->getSingleScalarResult();
	}
}
