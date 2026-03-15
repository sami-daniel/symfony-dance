<?php

namespace App\User\Controller;

use App\Shared\Http\BaseController;
use App\User\Commands\CreateNewUserCommand;
use App\User\Requests\CreateUserRequest;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Messenger\HandleTrait;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/users')]
class UserController extends BaseController
{
    use HandleTrait;

    public function __construct(
        private MessageBusInterface $messageBus,
    ) {
    }

    #[Route('', methods: ['POST'])]
    public function create(
        #[MapRequestPayload] CreateUserRequest $request,
    ): JsonResponse {
        $result = $this->handle(new CreateNewUserCommand($request));

        return $this->noContent();
    }
}
