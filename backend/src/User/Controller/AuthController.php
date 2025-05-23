<?php

declare(strict_types=1);

namespace App\User\Controller;

use App\Notifications\Enum\NotificationTypes;
use App\Notifications\Events\Notification;
use App\Telegram\Messages\TelegramNotification;
use App\Telegram\Services\TelegramBotService;
use App\User\Entity\AuthCode;
use App\User\Entity\User;
use App\User\Repository\AuthCodeRepository;
use App\User\Repository\UserRepository;
use App\Utils\Helpers;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;

class AuthController
{
    public function __construct(
        private EntityManagerInterface $em,
        private Security $security,
        private MessageBusInterface $bus,
        private UserRepository $userRepository,
        private AuthCodeRepository $authCodeRepository,
        private JWTTokenManagerInterface $jwtManager,
        private TelegramBotService $telegramService,
        private Helpers $helpers,
    ) {}

    #[Route('/api/auth/request-code', name: 'api_auth_request_code', methods: ['POST'])]
    public function requestCode(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $username = $data['username'] ?? null;

        if (!$username) {
            return new JsonResponse(
                ['success' => false, 'message' => 'Имя пользователя является обязательным'],
                JsonResponse::HTTP_BAD_REQUEST
            );
        }

        $user = $this->userRepository->findOneBy(['login' => $username]);

        try {
            $this->em->getConnection()->beginTransaction();
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

            $this->bus->dispatch(new TelegramNotification(
                $username,
                $verificationCode
            ));

            $this->bus->dispatch(new Notification(
                NotificationTypes::USER_REGISTERED->value,
                'Новая регистрация',
                'Пользователь ' . $username . ' зарегистрировался'
            ));

            $this->em->getConnection()->commit();
        } catch (\Exception $e) {
            $this->em->getConnection()->rollBack();
            throw $e;
        }

        return new JsonResponse(['success' => true, 'message' => 'Код для входа отправлен.']);
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
                    'message' => 'Имя пользователя и код являются обязательными',
                ],
                JsonResponse::HTTP_BAD_REQUEST
            );
        }

        $user = $this->userRepository->findOneBy(['login' => $username]);
        $authCode = $this->authCodeRepository->findOneBy(['code' => $submittedCode, 'user' => $user]);

        if (!$authCode) {
            return new JsonResponse(['message' => 'Неверный или просроченный код'], JsonResponse::HTTP_UNAUTHORIZED);
        }

        $user->setIsVerified(true);
        $this->em->persist($user);
        $this->em->flush();

        $token = $this->jwtManager->create($user);

        $this->bus->dispatch(new Notification(
            NotificationTypes::USER_VERIFIED->value,
            'Новая верификация',
            'Пользователь ' . $username . ' прошел верификацию'
        ));

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
                    'message' => 'Пользователь не найден',
                ],
                JsonResponse::HTTP_UNAUTHORIZED
            );
        }

        $user = $this->userRepository->findOneBy(['login' => $user->getLogin()]);
        if (!$user) {
            return new JsonResponse(
                [
                    'error' => true,
                    'message' => 'Неверный токен',
                ],
                JsonResponse::HTTP_UNAUTHORIZED
            );
        }

        return new JsonResponse(
            [
                'error' => false,
            ]
        );
    }
}
