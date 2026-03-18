<?php

declare(strict_types=1);

namespace App\Tests\Application\Concerns;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;

trait MakesHttpRequests
{
    private KernelBrowser $client;

    private const CONTENT_TYPE_JSON = ['CONTENT_TYPE' => 'application/json'];

    private function get(string $uri): void
    {
        $this->client->request('GET', $uri, server: self::CONTENT_TYPE_JSON);
    }

    /**
     * @param array<int,mixed> $body
     */
    private function post(string $uri, array $body = []): void
    {
        $this->client->request('POST', $uri, server: self::CONTENT_TYPE_JSON, content: json_encode($body));
    }

    /**
     * @param array<string,mixed> $body
     */
    private function put(string $uri, array $body = []): void
    {
        $this->client->request('PUT', $uri, server: self::CONTENT_TYPE_JSON, content: json_encode($body));
    }

    /**
     * @param array<int,mixed> $body
     */
    private function patch(string $uri, array $body = []): void
    {
        $this->client->request('PATCH', $uri, server: self::CONTENT_TYPE_JSON, content: json_encode($body));
    }

    private function delete(string $uri): void
    {
        $this->client->request('DELETE', $uri, server: self::CONTENT_TYPE_JSON);
    }

    private function responseJson(): array
    {
        return json_decode($this->client->getResponse()->getContent(), true);
    }
}
