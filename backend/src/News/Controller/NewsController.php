<?php

declare(strict_types=1);

namespace App\News\Controller;

use App\News\Repository\NewsRepository;
use App\Utils\Helpers;
use Predis\Client as RedisClient;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class NewsController
{
	public function __construct(
		private NewsRepository $newsRepository,
		private Helpers $helpers,
		private RedisClient $redis,
	) {
	}

	#[Route('/api/news', name: 'news_list', methods: ['GET'])]
	public function index(Request $request): JsonResponse
	{
		$page = max(1, (int) $request->query->get('page', 1));
		$limit = (int) $request->query->get('limit', 5);
		$search = (string) $request->query->get('search', '');

		$cacheKey = sprintf('news_list_page_%d_limit_%d_search_%s', $page, $limit, md5($search));

		if ($this->redis->exists($cacheKey)) {
			$cachedData = $this->redis->get($cacheKey);
			if ($cachedData) {
				return new JsonResponse(json_decode($cachedData, true));
			}
		}

		$offset = ($page - 1) * $limit;

		if ('' !== $search) {
			$news = $this->newsRepository->findBySearch($search, $limit, $offset);
			$total = $this->newsRepository->countBySearch($search);
		} else {
			$news = $this->newsRepository->getRecentNews($limit, $offset);
			$total = $this->newsRepository->count();
		}

		$data = array_map(fn ($item) => [
			'id' => $item->getId(),
			'title' => $this->helpers->truncateText($item->getTitle(), 150),
			'content' => $this->helpers->truncateText($item->getContent(), 450),
			'createdAt' => $item->getCreatedAt()->format('Y-m-d H:i:s'),
			'source' => $item->getSource(),
		], $news);

		$response = new JsonResponse(
			[
				'items' => $data,
				'total' => $total,
			],
		);

		$this->redis->setex($cacheKey, 3600, $response->getContent() ?: '');

		return $response;
	}
}
