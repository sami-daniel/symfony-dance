<?php

declare(strict_types=1);

namespace App\Auth\Controllers;

use App\Auth\Commands\AuthenticateUser\AuthenticateUserCommand;
use App\Auth\Commands\RefreshToken\RefreshTokenCommand;
use App\Auth\Entities\RefreshToken;
use App\Auth\Exceptions\InvalidCredentialsException;
use App\Auth\Inputs\LoginInput;
use App\Auth\Inputs\RefreshTokenInput;
use App\Auth\Outputs\LoginOutput;
use App\Shared\Http\BaseController;
use App\User\Entities\User;
use App\User\Exceptions\UserNotFound;
use App\User\Repositories\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\HandleTrait;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/auth')]
#[OA\Tag(name: 'Auth')]
class AuthController extends BaseController
{
    use HandleTrait;

    public function __construct(
        private MessageBusInterface $messageBus,
        private UserRepository $userRepository,
        private EntityManagerInterface $entityManager,
        private JWTTokenManagerInterface $jwtTokenManager,
    ) {
    }

    #[Route('/login', name: 'auth.login', methods: ['POST'])]
    #[OA\Post(summary: 'Authenticate a user and issue tokens')]
    #[OA\RequestBody(required: true, content: new Model(type: LoginInput::class))]
    #[OA\Response(response: 200, description: 'Authentication successful', content: new Model(type: LoginOutput::class))]
    #[OA\Response(response: 401, description: 'Invalid credentials')]
    #[OA\Response(response: 422, description: 'Validation failed')]
    public function login(
        #[MapRequestPayload] LoginInput $request,
    ): JsonResponse {
        try {
            $this->messageBus->dispatch(new AuthenticateUserCommand($request));
        } catch (ExceptionInterface $e) {
            $previous = $e->getPrevious();
            if ($previous instanceof UserNotFound || $previous instanceof InvalidCredentialsException) {
                return $this->json(['error' => 'Invalid credentials'], 401);
            }

            throw $e;
        }

        /** @var User $user */
        $user = $this->userRepository->findByEmail($request->email);

        $jwt = $this->jwtTokenManager->create($user);
        $rawToken = bin2hex(random_bytes(32));
        $refreshToken = new RefreshToken($user, $rawToken, new \DateTimeImmutable('+30 days'));

        $this->entityManager->persist($refreshToken);
        $this->entityManager->flush();

        return $this->ok(new LoginOutput($jwt, $rawToken));
    }

    #[Route('/refresh', name: 'auth.refresh', methods: ['POST'])]
    #[OA\Post(summary: 'Refresh JWT using a refresh token')]
    #[OA\RequestBody(required: true, content: new Model(type: RefreshTokenInput::class))]
    #[OA\Response(response: 200, description: 'Tokens refreshed', content: new Model(type: LoginOutput::class))]
    #[OA\Response(response: 401, description: 'Invalid or expired refresh token')]
    #[OA\Response(response: 422, description: 'Validation failed')]
    public function refresh(
        #[MapRequestPayload] RefreshTokenInput $request,
    ): JsonResponse {
        /** @var LoginOutput $output */
        $output = $this->handle(new RefreshTokenCommand($request));

        return $this->ok($output);
    }
}
