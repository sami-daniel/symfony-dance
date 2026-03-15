<?php

namespace App\User\Controller;

use App\Shared\Http\BaseController;
use App\User\Commands\CreateNewUserCommand;
use App\User\Exceptions\UserAlreadyExistsException;
use App\User\Inputs\CreateUserInput;
use App\User\Outputs\UserOutput;
use App\User\Queries\GetUserByEmail\GetUserByEmailQuery;
use App\User\Queries\GetUserByID\GetUserByIDQuery;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Messenger\HandleTrait;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/users')]
#[OA\Tag(name: 'Users')]
class UserController extends BaseController
{
    use HandleTrait;

    public function __construct(
        private MessageBusInterface $messageBus,
    ) {
    }

    #[Route('', methods: ['POST'])]
    #[OA\Post(summary: 'Create a new user')]
    #[OA\RequestBody(required: true, content: new Model(type: CreateUserInput::class))]
    #[OA\Response(response: 201, description: 'User created', content: new Model(type: UserOutput::class))]
    #[OA\Response(response: 422, description: 'Validation failed')]
    #[OA\Response(response: 409, description: 'User already exists')]
    public function createUser(
        #[MapRequestPayload] CreateUserInput $payload,
    ): JsonResponse {
        $this->messageBus->dispatch(new CreateNewUserCommand($payload));

        try {
            $email = $payload->email;
            /** @var UserOutput $user */
            $user = $this->handle(new GetUserByEmailQuery($email));
        } catch (UserAlreadyExistsException $e) {
            return $this->conflict("An user with {$email} email already exists");
        }

        return $this->created($user, $this->generateUrl('users.get', ['id' => $user->id]));
    }

    #[Route('/{id}', name: 'users.get', methods: ['GET'])]
    #[OA\Get(summary: 'Get a user by ID')]
    #[OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))]
    #[OA\Response(response: 200, description: 'User found', content: new Model(type: UserOutput::class))]
    #[OA\Response(response: 404, description: 'User not found')]
    public function getUserById(int $id): JsonResponse
    {
        $user = $this->handle(new GetUserByIDQuery($id));

        if (!$user) {
            return $this->notFound();
        }

        return $this->ok($user);
    }
}
