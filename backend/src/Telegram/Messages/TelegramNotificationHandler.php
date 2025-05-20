<?php
declare(strict_types=1);

namespace App\Telegram\Messages;

use App\Telegram\Services\TelegramBotService;
use App\Telegram\Messages\TelegramNotification;

class TelegramNotificationHandler
{
    public function __construct(
        private TelegramBotService $telegramService
    ) {}

    public function __invoke(TelegramNotification $message): void
    {
        $result = $this->telegramService->sendMessage(
            sprintf(
                '%s, ваш код для входа в систему: %s',
                $message->username,
                $message->authCode
            )
        );
        if (!$result) {
            throw new \Exception('Не удалось отправить сообщение с кодом в Telegram');
        }
    }
}