<?php

declare(strict_types=1);

namespace App\Telegram\Messages;

class TelegramNotification
{
	public function __construct(
		public readonly string $username,
		public readonly string $authCode,
	) {
	}
}
