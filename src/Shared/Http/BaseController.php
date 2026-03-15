<?php

namespace App\Shared\Http;

use Saboohy\HttpStatus\Client;
use Saboohy\HttpStatus\Success;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;

abstract class BaseController extends AbstractController
{
    protected function noContent(): JsonResponse
    {
        return $this->json(null, Success::NO_CONTENT->value);
    }

    protected function created(mixed $created, ?string $location = null): JsonResponse
    {
        $headers = $location ? [
            'Location' => $location,
        ] : [];

        return $this->json($created, Success::CREATED->value, $headers);
    }

    protected function ok(mixed $data): JsonResponse
    {
        return $this->json($data, Success::OK->value);
    }

    protected function notFound(?string $errorMessage = null): JsonResponse
    {
        $data = $errorMessage ? [
            'error' => $errorMessage,
        ] : null;

        return $this->json($data, Client::NOT_FOUND->value);
    }

    protected function conflict(?string $errorMessage = null): JsonResponse
    {
        $data = $errorMessage ? [
            'error' => $errorMessage,
        ] : null;

        return $this->json($data, Client::CONFLICT->value);
    }
}
