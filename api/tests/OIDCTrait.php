<?php

declare(strict_types=1);

namespace App\Tests;

use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Key;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Token;

trait OIDCTrait
{
    private function getToken(string $iss, string $email, string $issuedAt = 'now', string $ttl = '+30 seconds'): Token
    {
        /** @var Configuration $configuration */
        $configuration = self::getContainer()->get(Configuration::class);
        $now = new \DateTimeImmutable($issuedAt);

        return $configuration->builder()
            ->issuedBy($iss)
            ->withHeader('iss', $iss)
            ->permittedFor($iss)
            ->issuedAt($now)
            ->expiresAt($now->modify($ttl))
            ->withClaim('email', $email)
            ->getToken($configuration->signer(), $this->getPrivateKey());
    }

    private function getPrivateKey(): Key
    {
        return InMemory::plainText(file_get_contents(self::getContainer()->getParameter('kernel.project_dir').'/docker/keycloak/keycloak.key'));
    }
}
