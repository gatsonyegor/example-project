<?php

declare(strict_types=1);

namespace App\Notifications\Services;

use App\Notifications\Repository\NotificationRepository;
use App\Notifications\Entity\Notification;
use Symfony\Bundle\SecurityBundle\Security;
use Doctrine\ORM\EntityManagerInterface;

class NotificationService
{
    private const NOTIFICATION_STREAM_INTERVAL = 5;

    public function __construct(
        private NotificationRepository $notificationRepository,
        private Security $security,
        private EntityManagerInterface $em
    ) {}

    public function getStreamedNotificationsCallback(): void
    {
        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');
        header('Connection: keep-alive');
        header('X-Accel-Buffering: no');

        if (ob_get_level()) {
            ob_end_clean();
        }

        while (true) {
            $user = $this->security->getUser();

            if (!$user) {
                return;
            }
            
            $notifications = $this->notificationRepository->getUnreadNotifications();
            if (empty($notifications)) {
                echo "event: ping\n";
                echo 'data: ' . json_encode(['timestamp' => time()]) . "\n\n";
                flush();
                sleep(self::NOTIFICATION_STREAM_INTERVAL);
                continue;
            }

            echo "event: message\n";
            echo 'data: ' . json_encode($notifications) . "\n\n";
            flush();

            if (connection_aborted()) {
                break;
            }

            $this->markAsRead($notifications);

            sleep(self::NOTIFICATION_STREAM_INTERVAL);
        }
    }

    /**
     * @param Notification[] $notifications
     */
    public function markAsRead(array $notifications): void
    {
        $ids = array_column($notifications, 'id');

        $this->notificationRepository->markAsRead($ids);
    }
}
