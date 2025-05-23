<?php

declare(strict_types=1);

namespace App\Notifications\Services;

use Predis\Client as RedisClient;

class NotificationService
{
	private const NOTIFICATION_STREAM_INTERVAL = 10000;

	public function __construct(
		private RedisClient $redis,
	) {
	}

	public function getStreamedNotificationsCallback(): void
	{
		header('Content-Type: text/event-stream');
		header('Cache-Control: no-cache');
		header('Connection: keep-alive');
		header('X-Accel-Buffering: no');

		if (ob_get_level()) {
			ob_end_clean();
		}

		$notificationsRedisSub = $this->redis->pubSubLoop();
		$notificationsRedisSub->subscribe('global_notificaions');

		$isMessagesExist = false;
		while (true) {
			foreach ($notificationsRedisSub as $message) {
				if ('message' !== $message->kind) {
					continue;
				}
				$isMessagesExist = true;
				echo "event: message\n";
				echo 'data: '.json_encode($message->payload)."\n\n";
				flush();
			}

			if (!$isMessagesExist) {
				echo "event: ping\n";
				echo 'data: '.json_encode(['timestamp' => time()])."\n\n";
				flush();
				sleep(self::NOTIFICATION_STREAM_INTERVAL);
				continue;
			}

			$isMessagesExist = false;

			if (connection_aborted()) {
				break;
			}

			sleep(self::NOTIFICATION_STREAM_INTERVAL);
		}

		$notificationsRedisSub->unsubscribe();
	}
}
