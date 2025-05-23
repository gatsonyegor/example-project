<?php

declare(strict_types=1);

namespace App\User\Controller;

use App\User\Entity\User;
use App\User\Repository\UserRepository;
use Predis\Client as RedisClient;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class UserController
{
	public function __construct(
		private UserRepository $userRepository,
		private RedisClient $redis,
	) {
	}

	#[Route('/api/user', name: 'api_user_check', methods: ['GET'])]
	public function checkUser(Request $request): JsonResponse
	{
		$user = $this->userRepository->findOneBy(['login' => $request->query->get('login')]);

		if (!$user) {
			return new JsonResponse(['error' => true], JsonResponse::HTTP_NOT_FOUND);
		}

		return new JsonResponse(['error' => false]);
	}

	#[Route('/api/user/list', name: 'api_user_list', methods: ['GET'])]
	public function list(Request $request): JsonResponse
	{
		$page = max(1, (int) $request->query->get('page', 1));
		$limit = (int) $request->query->get('limit', 5);
		$offset = ($page - 1) * $limit;

		$cacheKey = sprintf('users_list_page_%d_limit_%d', $page, $limit);

		if ($this->redis->exists($cacheKey)) {
			$cachedData = $this->redis->get($cacheKey);
			if ($cachedData) {
				return new JsonResponse(json_decode($cachedData, true));
			}
		}

		$users = $this->userRepository->findBy([], null, $limit, $offset);
		$total = $this->userRepository->count();

		$data = array_map(function (User $user) {
			return [
				'id' => $user->getId(),
				'login' => $user->getLogin(),
				'isVerified' => $user->getIsVerified(),
				'lastVerificationCode' => $user->getLatestAuthCode()?->getCode(),
			];
		}, $users);

		$response = new JsonResponse([
			'items' => $data,
			'total' => $total,
		]);

		$this->redis->setex($cacheKey, 3600, $response->getContent() ?: '');

		return $response;
	}
}
