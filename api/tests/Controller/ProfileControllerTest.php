<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Symfony\Bundle\Test\Client;
use App\Controller\ProfileController;
use App\Tests\Api\RefreshDatabaseTrait;
use App\Tests\OIDCTrait;
use Lcobucci\JWT\Configuration;

/**
 * @see ProfileController
 */
final class ProfileControllerTest extends ApiTestCase
{
    use OIDCTrait;
    use RefreshDatabaseTrait;

    private Client $client;

    protected function setup(): void
    {
        $this->client = self::createClient();
    }

    /**
     * @see ProfileController::__invoke()
     *
     * @dataProvider getUsers
     */
    public function testProfile(string $email, array $roles): void
    {
        $this->client->request('GET', '/profile', [
            'auth_bearer' => $this->getToken('http://localhost:8080/realms/demo', $email)->toString(),
        ]);
        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('content-type', 'application/json');
        self::assertJsonContains([
            'email' => $email,
            'roles' => $roles,
        ]);
    }

    public function getUsers(): iterable
    {
        yield ['user@example.com', ['ROLE_USER']];
        yield ['admin@example.com', ['ROLE_ADMIN']];
    }

    /**
     * Token is expired since 30 seconds.
     */
    public function testCannotGetProfileWithExpiredToken(): void
    {
        $this->client->request('GET', '/profile', [
            'auth_bearer' => $this->getToken('http://localhost:8080/realms/demo', 'user@example.com', '-30 seconds')->toString(),
        ]);
        self::assertResponseStatusCodeSame(401);
    }

    /**
     * Custom claim "email" is missing.
     */
    public function testCannotGetProfileWithInvalidToken(): void
    {
        /** @var Configuration $configuration */
        $configuration = self::getContainer()->get(Configuration::class);
        $now = new \DateTimeImmutable('now');

        $token = $configuration->builder()
            ->issuedBy('http://localhost:8080/realms/demo')
            ->withHeader('iss', 'http://localhost:8080/realms/demo')
            ->permittedFor('http://localhost:8080/realms/demo')
            ->issuedAt($now)
            ->expiresAt($now->modify('+30 seconds'))
            ->getToken($configuration->signer(), $this->getPrivateKey());

        $this->client->request('GET', '/profile', [
            'auth_bearer' => $token->toString(),
        ]);
        self::assertResponseStatusCodeSame(401);
    }
}
