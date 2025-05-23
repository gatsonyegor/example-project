<?php

declare(strict_types=1);

namespace App\News\Controller;

use App\News\Repository\NewsRepository;
use App\Utils\Helpers;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class NewsController
{
	public function __construct(
		private NewsRepository $newsRepository,
		private Helpers $helpers,
	) {
	}

	#[Route('/api/news', name: 'news_list', methods: ['GET'])]
	public function index(Request $request): JsonResponse
	{
		$page = max(1, (int) $request->query->get('page', 1));
		$limit = (int) $request->query->get('limit', 5);
		$search = (string) $request->query->get('search', '');

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

		return new JsonResponse(
			[
				'items' => $data,
				'total' => $total,
			],
		);
	}
}
