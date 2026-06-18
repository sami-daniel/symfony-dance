<?php

declare(strict_types=1);

namespace App\Auth\Controllers;

use App\Auth\Commands\AuthenticateUser\AuthenticateUserCommand;
use App\Auth\Commands\RefreshToken\RefreshTokenCommand;
use App\Auth\Exceptions\InvalidCredentialsException;
use App\Auth\Exceptions\InvalidRefreshTokenException;
use App\Auth\Inputs\LoginInput;
use App\Auth\Inputs\RefreshTokenInput;
use App\Auth\Outputs\LoginOutput;
use App\Shared\Http\BaseController;
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
        /** @phpstan-ignore property.onlyWritten */
        private MessageBusInterface $messageBus,
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
            return $this->ok($this->handle(new AuthenticateUserCommand($request)));
        } catch (ExceptionInterface $e) {
            $previous = $e->getPrevious();

            return match (true) {
                $previous instanceof InvalidCredentialsException => $this->unathorized('Invalid email or password'),
                default => throw $e,
            };
        }
    }

    #[Route('/refresh', methods: ['POST'])]
    #[OA\Post(summary: 'Rotates and refreshes the jwt token and the refresh token')]
    #[OA\RequestBody(required: true, content: new Model(type: RefreshTokenInput::class))]
    #[OA\Response(response: 200, description: 'Token refreshed with success', content: new Model(type: LoginOutput::class))]
    #[OA\Response(response: 401, description: 'Invalid or expired refresh token')]
    #[OA\Response(response: 422, description: 'Validation failed')]
    public function refresh(
        #[MapRequestPayload] RefreshTokenInput $request,
    ): JsonResponse {
        try {
            return $this->ok($this->handle(new RefreshTokenCommand($request->refreshToken)));
        } catch (ExceptionInterface $e) {
            $previous = $e->getPrevious();

            return match (true) {
                $previous instanceof InvalidRefreshTokenException => $this->unathorized('Invalid or expired refresh token'),
                default => throw $e,
            };
        }
    }
}
