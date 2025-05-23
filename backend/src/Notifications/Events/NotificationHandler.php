<?php

declare(strict_types=1);

namespace App\Notifications\Events;

use Predis\Client as RedisClient;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class NotificationHandler
{
	public function __construct(
		private RedisClient $redis,
	) {
	}

	public function __invoke(Notification $notification): void
	{
		$this->redis->publish('global_notificaions', json_encode([
			'type' => $notification->getType(),
			'title' => $notification->getTitle(),
			'message' => $notification->getMessage(),
		]));
	}
}
