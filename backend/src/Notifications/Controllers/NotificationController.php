<?php

namespace App\Notifications\Controllers;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Messenger\MessageBusInterface;
use App\Notifications\Services\NotificationService;

class NotificationController
{
    public function __construct(
        private Security $security,
        private MessageBusInterface $messageBus,
        private NotificationService $notificationService
    ) {
    }

    #[Route('/api/notifications/stream', name: 'notifications_stream', methods: ['GET'])]
    public function streamNotifications(): Response
    {
        $response = new StreamedResponse();
        $response->setCallback([$this->notificationService, 'getStreamedNotificationsCallback']);

        $response->headers->set('Content-Type', 'text/event-stream');
        $response->headers->set('Cache-Control', 'no-cache');
        $response->headers->set('Connection', 'keep-alive');
        $response->headers->set('X-Accel-Buffering', 'no');

        return $response;
    }
} 