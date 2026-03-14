<?php

namespace App\User\Controller;

use App\Shared\Http\BaseController;
use App\User\Commands\CreateNewUserCommand;
use App\User\Requests\CreateUserRequest;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/users')]
class UserController extends BaseController
{
    public function __construct(private MessageBusInterface $bus)
    {
    }

    #[Route('', methods: ['POST'])]
    public function create(
        #[MapRequestPayload] CreateUserRequest $request,
    ): JsonResponse {
        $val = $this->bus->dispatch(new CreateNewUserCommand($request));

        return $this->json($val, 200);
    }
}
