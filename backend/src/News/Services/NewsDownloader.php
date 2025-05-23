<?php

declare(strict_types=1);

namespace App\News\Services;

use App\News\Entity\News;
use App\Notifications\Enum\NotificationTypes;
use App\Notifications\Events\Notification;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Client;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;

class NewsDownloader
{
	private const NEWS_API_URL = 'https://newsapi.org/v2/everything';

	private const NEWS_SOURCES = [
		'lenta' => 'lenta.ru',
		'rbc' => 'rbc.ru',
		'fox-sports' => 'foxsports.com',
		'business-insider' => 'businessinsider.com',
		'buzzfeed' => 'buzzfeed.com',
	];

	public function __construct(
		private string $newsApiKey,
		private Client $client,
		private EntityManagerInterface $em,
		private MessageBusInterface $bus,
	) {
	}

	public function handle()
	{
		$tz = new \DateTimeZone('Europe/Moscow');
		$yesterday = (new \DateTime('yesterday', $tz))->format('Y-m-d');
		$today = (new \DateTime('now', $tz))->format('Y-m-d');

		$response = $this->client->request(
			'GET',
			self::NEWS_API_URL,
			[
				'query' => [
					'apiKey' => $this->newsApiKey,
					'sources' => 'rbc,lenta,bbc,fox-sports,business-insider,buzzfeed',
					'sortBy' => 'publishedAt',
					'from' => $yesterday,
					'to' => $today,
				],
			]
		);

		if (Response::HTTP_OK !== $response->getStatusCode()) {
			return false;
		}

		$newsData = json_decode($response->getBody()->getContents(), true);

		if (0 === count($newsData['articles']) || 'ok' !== $newsData['status']) {
			return false;
		}

		foreach ($newsData['articles'] as $article) {
			$news = new News();
			$news->setTitle($article['title']);
			$news->setContent($article['description']);
			$news->setSource(self::NEWS_SOURCES[$article['source']['id']]);

			$articleCreatedAt = (new \DateTimeImmutable($article['publishedAt']));
			$news->setCreatedAt($articleCreatedAt);

			$this->em->persist($news);
			$this->em->flush();
		}

		$this->bus->dispatch(new Notification(
			NotificationTypes::NEWS_UPDATED->value,
			'Обновление новостей',
			'Новости за сутки успешно загружены'
		));
	}
}
