<?php

declare(strict_types=1);

namespace App\Shared\Http;

use Saboohy\HttpStatus\Client;
use Saboohy\HttpStatus\Success;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;

abstract class BaseController extends AbstractController
{
    /** @return array<string, string>|null */
    private function mountError(?string $e): ?array
    {
        return $e ? [
            'error' => $e,
        ] : null;
    }

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
        return $this->json($this->mountError($errorMessage), Client::NOT_FOUND->value);
    }

    protected function conflict(?string $errorMessage = null): JsonResponse
    {
        return $this->json($this->mountError($errorMessage), Client::CONFLICT->value);
    }

    protected function unathorized(?string $errorMessage = null): JsonResponse
    {
        return $this->json($this->mountError($errorMessage), Client::UNAUTHORIZED->value);
    }
}
