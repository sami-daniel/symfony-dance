<?php

namespace App\Shared\Http;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;

abstract class BaseController extends AbstractController
{
    protected function noContent(): JsonResponse
    {
        return $this->json(null, 204);
    }
}
