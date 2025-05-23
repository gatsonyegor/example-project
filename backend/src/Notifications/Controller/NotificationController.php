<?php

namespace App\Notifications\Controller;

use App\Notifications\Enum\NotificationTypes;
use App\Notifications\Events\Notification;
use App\Notifications\Services\NotificationService;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;

class NotificationController
{
	public function __construct(
		private Security $security,
		private MessageBusInterface $messageBus,
		private NotificationService $notificationService,
	) {
	}

	#[Route('/api/notification', name: 'notifications_test', methods: ['GET'])]
	public function testNotification(): Response
	{
		$this->messageBus->dispatch(new Notification(
			NotificationTypes::USER_REGISTERED->value,
			'Новая регистрация',
			'Пользователь зарегистрировался'
		));

		return new JsonResponse(
			[
				'result' => 1,
			]
		);
	}

	#[Route('/api/notifications/stream', name: 'notifications_stream', methods: ['GET'])]
	public function streamNotifications(): Response
	{
		$user = $this->security->getUser();

		if (!$user) {
			return new Response('пользователь не найден', 401);
		}

		$response = new StreamedResponse();
		$response->setCallback(
			function () {
				$this->notificationService->getStreamedNotificationsCallback();
			}
		);

		$response->headers->set('Content-Type', 'text/event-stream');
		$response->headers->set('Cache-Control', 'no-cache');
		$response->headers->set('Connection', 'keep-alive');
		$response->headers->set('X-Accel-Buffering', 'no');

		return $response;
	}
}
