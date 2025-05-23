<?php

declare(strict_types=1);

namespace App\Notifications\Repository;

use App\Notifications\Entity\Notification;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class NotificationRepository extends ServiceEntityRepository
{
	public function __construct(ManagerRegistry $registry)
	{
		parent::__construct($registry, Notification::class);
	}

	public function getUnreadNotifications(): array
	{
		return $this->createQueryBuilder('n')
			->select('n.id', 'n.title', 'n.message', 'n.type', 'n.createdAt')
			->where('n.isShown = :isShown')
			->setParameter('isShown', false)
			->orderBy('n.createdAt', 'DESC')
			->getQuery()
			->getResult();
	}

	public function markAsRead(array $notificationIds): void
	{
		$this->createQueryBuilder('n')
		   ->update()
		   ->set('n.isShown', true)
		   ->where('n.id IN (:ids)')
		   ->setParameter('ids', $notificationIds)
		   ->getQuery()
		   ->execute();
	}
}
