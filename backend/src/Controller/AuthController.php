<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use App\Repository\UserRepository;
use App\Services\TelegramBotService;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\AuthCodeRepository;
use App\Services\Helpers;
use App\Entity\AuthCode;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Bundle\SecurityBundle\Security;
class AuthController
{
    public function __construct(
        private UserRepository $userRepository,
        private AuthCodeRepository $authCodeRepository,
        private JWTTokenManagerInterface $jwtManager,
        private TelegramBotService $telegramService,
        private EntityManagerInterface $em,
        private Helpers $helpers,
        private Security $security,
    ) {}

    #[Route('/api/auth/request-code', name: 'api_auth_request_code', methods: ['POST'])]
    public function requestCode(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $username = $data['username'] ?? null;

        if (!$username) {
            return new JsonResponse(['success' => false, 'message' => 'Username is required'], JsonResponse::HTTP_BAD_REQUEST);
        }

        $user = $this->userRepository->findOneBy(['login' => $username]);

        $this->em->getConnection()->beginTransaction();
        try {
            if (!$user) {
                $user = new User();
                $user->setLogin($username);
                $this->em->persist($user);
                $this->em->flush();
            }

            $verificationCode = $this->helpers->generateVerificationCode();
            $authCode = new AuthCode();
            $authCode->setCode($verificationCode);
            $authCode->setUser($user);
            $this->em->persist($authCode);
            $this->em->flush();

            $this->telegramService->sendVerificationCode($username, $authCode->getCode());
        } catch (\Exception $e) {
            $this->em->getConnection()->rollBack();
            throw $e;
        }
        $this->em->getConnection()->commit();

        return new JsonResponse(['success' => true, 'message' => 'Verification code sent.']);
    }

    #[Route('/api/auth/verify-code', name: 'api_auth_verify_code', methods: ['POST'])]
    public function verifyCode(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $username = $data['username'] ?? null;
        $submittedCode = $data['code'] ?? null;

        if (!$username || !$submittedCode) {
            return new JsonResponse(
                [
                    'message' => 'Username and code are required'
                ],
                JsonResponse::HTTP_BAD_REQUEST
            );
        }

        $user = $this->userRepository->findOneBy(['login' => $username]);
        $authCode = $this->authCodeRepository->findOneBy(['code' => $submittedCode, 'user' => $user]);

        if (!$authCode) {
            return new JsonResponse(['message' => 'Invalid or expired verification code'], JsonResponse::HTTP_UNAUTHORIZED);
        }

        $user->setIsVerified(true);
        $this->em->persist($user);
        $this->em->flush();

        $token = $this->jwtManager->create($user);

        return new JsonResponse(['token' => $token]);
    }

    #[Route('/api/auth/validate-token', name: 'api_auth_validate_token', methods: ['POST'])]
    public function validateToken(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $this->security->getUser();

        if (!$user) {
            return new JsonResponse(
                [
                    'error' => true,
                    'message' => 'No user found'
                ],
                JsonResponse::HTTP_UNAUTHORIZED
            );
        }

        $user = $this->userRepository->findOneBy(['login' => $user->getLogin()]);
        if (!$user) {
            return new JsonResponse(
                [
                    'error' => true,
                    'message' => 'Invalid token'
                    ],
                    JsonResponse::HTTP_UNAUTHORIZED
                );
            }

        return new JsonResponse(
            [
                'error' => false
            ]
        );

        // $data = json_decode($request->getContent(), true);
        // $token = $data['token'] ?? null;
        // if (!$token) {
        //     return new JsonResponse(
        //         [
        //             'error' => true,
        //             'message' => 'No token provided'
        //         ],
        //         JsonResponse::HTTP_BAD_REQUEST
        //     );
        // }

        // try {
        //     $decodedToken = $this->jwtManager->parse($token);

        //     if (!$decodedToken['username']) {
        //         return new JsonResponse(
        //             [
        //                 'error' => true,
        //                 'message' => 'Invalid token'
        //             ],
        //             JsonResponse::HTTP_UNAUTHORIZED
        //         );
        //     }

        //     $user = $this->userRepository->findOneBy(['login' => $decodedToken['username']]);
        //     if (!$user) {
        //         return new JsonResponse(
        //             [
        //                 'error' => true,
        //                 'message' => 'Invalid token'
        //             ],
        //             JsonResponse::HTTP_UNAUTHORIZED
        //         );
        //     }
        // } catch (\Exception $e) {
        //     return new JsonResponse(
        //         [
        //             'error' => true,
        //             'message' => 'Token validation failed' . $e->getMessage()
        //         ],
        //         JsonResponse::HTTP_UNAUTHORIZED
        //     );
        // }

        // return new JsonResponse(
        //     [
        //         'error' => false,
        //     ]
        // );
    }
}
