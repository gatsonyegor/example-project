<?php
declare(strict_types=1);

namespace App\Services;

use App\Entity\News;
use DateTime;
use DateTimeImmutable;
use DateTimeZone;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Client;
use Symfony\Component\HttpFoundation\Response;

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
        private EntityManagerInterface $em
    ) {}

    public function handle()
    {
        $tz    = new DateTimeZone('Europe/Moscow');
        $yesterday = (new DateTime('yesterday', $tz))->format('Y-m-d');
        $today = (new DateTime('now', $tz))->format('Y-m-d');

        $response = $this->client->request(
            'GET',
            self::NEWS_API_URL,
            [
                'query' => [
                    'apiKey' => $this->newsApiKey,
                    'sources' => 'rbc,lenta,bbc,fox-sports,business-insider,buzzfeed',
                    'sortBy' => 'publishedAt',
                    'from' => $yesterday,
                    'to' => $today
                ],
            ]
        );

        if ($response->getStatusCode() !== Response::HTTP_OK) {
            return false;
        }

        $newsData = json_decode($response->getBody()->getContents(), true);

        if (count($newsData['articles']) === 0 || $newsData['status'] !== 'ok') {
            return false;
        }

        foreach ($newsData['articles'] as $article) {
            $news = new News;
            $news->setTitle($article['title']);
            $news->setContent($article['description']);
            $news->setSource(self::NEWS_SOURCES[$article['source']['id']]);

            $articleCreatedAt = (new DateTimeImmutable($article['publishedAt']));
            $news->setCreatedAt($articleCreatedAt);

            $this->em->persist($news);
            $this->em->flush();
        }
    }
}

