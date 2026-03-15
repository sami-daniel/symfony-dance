<?php

namespace App\User\Controller;

use App\Shared\Http\BaseController;
use App\User\Commands\CreateNewUserCommand;
use App\User\Entity\User;
use App\User\Queries\GetUserByEmail\GetUserByEmailQuery;
use App\User\Queries\GetUserByID\GetUserByIDQuery;
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
    public function createUser(
        #[MapRequestPayload] CreateUserRequest $request,
    ): JsonResponse {
        $this->messageBus->dispatch(new CreateNewUserCommand($request));
        /** @var User $user */
        $user = $this->handle(new GetUserByEmailQuery($request->email));

        return $this->created($user, $this->generateUrl('users.get', ['id' => $user->getId()]));
    }

    #[Route('/{id}', name: 'users.get', methods: ['GET'])]
    public function getUserById(int $id): JsonResponse
    {
        $user = $this->handle(new GetUserByIDQuery($id));

        if (!$user) {
            return $this->notFound();
        }

        return $this->ok($user);
    }
}
