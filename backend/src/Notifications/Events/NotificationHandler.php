<?php
declare(strict_types=1);

namespace App\Notifications\Events;

use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use App\Notifications\Entity\Notification as NotificationEntity;
use Doctrine\ORM\EntityManagerInterface;

#[AsMessageHandler]
class NotificationHandler
{
    public function __construct(
        private EntityManagerInterface $em
    ) {
    }

    public function __invoke(Notification $notification): void
    {
        $notificationEntity = new NotificationEntity();
        $notificationEntity->setType($notification->getType());
        $notificationEntity->setTitle($notification->getTitle());
        $notificationEntity->setMessage($notification->getMessage());
        $this->em->persist($notificationEntity);
        $this->em->flush();
    }
}