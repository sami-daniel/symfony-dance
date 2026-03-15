<?php

namespace App\Shared\Http;

use Saboohy\HttpStatus\Success;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;

abstract class BaseController extends AbstractController
{
    protected function noContent(): JsonResponse
    {
        return $this->json(null, Success::NO_CONTENT);
    }

    protected function created(mixed $created, ?string $location): JsonResponse
    {
        $headers = $location ? [
            'Location' => $location,
        ] : [];

        return $this->json($created, Success::CREATED, $headers);
    }
}
