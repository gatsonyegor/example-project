<?php
declare(strict_types=1);

namespace App\Services;

use GuzzleHttp\Client;
use Symfony\Component\HttpFoundation\Response;

class TelegramBotService
{
    public function __construct(
        private string $botToken,
        private string $groupId,
        private Client $client,
        private Helpers $helpers
    ) {}

    public function sendMessage(string $message): bool
    {
        $telegramUrl = sprintf('https://api.telegram.org/bot%s/sendMessage', $this->botToken);

        $response = $this->client->request(
            'GET',
            $telegramUrl,
            [
                'query' => [
                    'chat_id' => $this->groupId,
                    'text' => $message
                ],
            ]
        );

        if ($response->getStatusCode() !== Response::HTTP_OK) {
            return false;
        }


        return json_decode($response->getBody()->getContents(), true)['ok'] === true;
    }

    public function sendVerificationCode(string $username, string $verificationCode): bool
    {
        $message = "$username, ваш код для входа в систему: $verificationCode";
        return $this->sendMessage($message);
    }
}
