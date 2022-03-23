<?php

declare(strict_types=1);

namespace App\Security;

use App\Exception\InvalidUserException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Http\AccessToken\AccessTokenHandlerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Send token to Keycloak to validate it.
 *
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 *
 * @final
 */
class KeycloakAccessTokenHandler implements AccessTokenHandlerInterface
{
    public function __construct(private readonly HttpClientInterface $oidcClient, private readonly LoggerInterface $logger)
    {
    }

    public function getUserIdentifierFrom(string $accessToken): string
    {
        try {
            // Call Keycloak to retrieve user info
            // If the token is invalid or expired, Keycloak will return an error
            $userinfo = $this->oidcClient->request('GET', 'protocol/openid-connect/userinfo', [
                'auth_bearer' => $accessToken,
            ])->toArray();
            if (empty($userinfo['email'])) {
                throw new InvalidUserException('"email" property not found on Keycloak response.');
            }

            return $userinfo['email'];
        } catch (\Throwable $e) {
            $this->logger->error('An error occurred on Keycloak.', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw new BadCredentialsException('Invalid credentials.', $e->getCode(), $e);
        }
    }
}
