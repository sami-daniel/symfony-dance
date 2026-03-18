<?php

declare(strict_types=1);

namespace App\Tests\Application;

use App\Tests\Application\Concerns\MakesHttpRequests;
use App\User\Entities\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AuthControllerTest extends WebTestCase
{
    use MakesHttpRequests;

    private EntityManagerInterface $em;
    private User $user;

    protected function setUp(): void
    {
        $this->client = static::createClient();

        $container = static::getContainer();
        $this->em = $container->get(EntityManagerInterface::class);

        $this->em->createQuery('DELETE FROM App\User\Entities\User u WHERE u.email = :email')
            ->setParameter('email', 'test@example.com')
            ->execute();

        /** @var UserPasswordHasherInterface $hasher */
        $hasher = $container->get(UserPasswordHasherInterface::class);

        $this->user = new User('Test User', 'test@example.com', '');
        $this->user->setPassword($hasher->hashPassword($this->user, 'Secret123'));

        $this->em->persist($this->user);
        $this->em->flush();
    }

    protected function tearDown(): void
    {
        try {
            $this->em->createQuery('DELETE FROM App\User\Entities\User u WHERE u.email = :email')
                ->setParameter('email', 'test@example.com')
                ->execute();
        } finally {
            parent::tearDown();
        }
    }

    // ==================== Login ====================

    public function testLoginReturnsTokensOnValidCredentials(): void
    {
        $this->post('/api/auth/login', ['email' => 'test@example.com', 'password' => 'Secret123']);

        $this->assertResponseIsSuccessful();

        $data = $this->responseJson();
        $this->assertArrayHasKey('token', $data);
        $this->assertArrayHasKey('refreshToken', $data);
        $this->assertNotEmpty($data['token']);
        $this->assertSame(64, strlen($data['refreshToken']));
    }

    public function testLoginReturnsUnauthorizedOnWrongPassword(): void
    {
        $this->post('/api/auth/login', ['email' => 'test@example.com', 'password' => 'WrongPassword']);

        $this->assertResponseStatusCodeSame(401);
    }

    public function testLoginReturnsUnprocessableOnMissingFields(): void
    {
        $this->post('/api/auth/login', ['email' => 'test@example.com']);

        $this->assertResponseStatusCodeSame(422);
    }

    // ==================== Refresh ====================

    public function testRefreshReturnsNewTokens(): void
    {
        $this->post('/api/auth/login', ['email' => 'test@example.com', 'password' => 'Secret123']);
        $loginData = $this->responseJson();

        $this->post('/api/auth/refresh', ['refreshToken' => $loginData['refreshToken']]);

        $this->assertResponseIsSuccessful();

        $refreshData = $this->responseJson();
        $this->assertArrayHasKey('token', $refreshData);
        $this->assertArrayHasKey('refreshToken', $refreshData);
        $this->assertNotSame($loginData['refreshToken'], $refreshData['refreshToken']);
    }

    public function testRefreshInvalidatesOldToken(): void
    {
        $this->post('/api/auth/login', ['email' => 'test@example.com', 'password' => 'Secret123']);
        $loginData = $this->responseJson();
        $oldToken = $loginData['refreshToken'];

        $this->post('/api/auth/refresh', ['refreshToken' => $oldToken]);

        $this->post('/api/auth/refresh', ['refreshToken' => $oldToken]);

        $this->assertResponseStatusCodeSame(401);
    }

    public function testRefreshReturnsUnauthorizedOnInvalidToken(): void
    {
        $this->post('/api/auth/refresh', ['refreshToken' => 'invalid-token-string']);

        $this->assertResponseStatusCodeSame(401);
    }

    public function testRefreshReturnsUnprocessableOnMissingFields(): void
    {
        $this->post('/api/auth/refresh', []);

        $this->assertResponseStatusCodeSame(422);
    }
}
