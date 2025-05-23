<?php

declare(strict_types=1);

namespace App\User\Controller;

use App\User\Entity\User;
use App\User\Repository\UserRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class UserController
{
	public function __construct(
		private UserRepository $userRepository,
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

		return new JsonResponse([
			'items' => $data,
			'total' => $total,
		]);
	}
}
