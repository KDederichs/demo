<?php

declare(strict_types=1);

namespace App\Security;

use App\JWT\Validation\Constraint\HasClaim;
use Lcobucci\Clock\SystemClock;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\UnencryptedToken;
use Lcobucci\JWT\Validation\Constraint\LooseValidAt;
use Lcobucci\JWT\Validation\Constraint\SignedWith;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Http\AccessToken\AccessTokenHandlerInterface;

/**
 * Parse and validate token manually.
 * Decorates KeycloakAccessTokenHandler on dev environment for local usage.
 *
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 *
 * @final
 */
class AccessTokenHandler implements AccessTokenHandlerInterface
{
    public function __construct(private readonly AccessTokenHandlerInterface $decorated, private readonly Configuration $configuration)
    {
    }

    public function getUserIdentifierFrom(string $accessToken): string
    {
        try {
            try {
                return $this->decorated->getUserIdentifierFrom($accessToken);
            } catch (\Throwable) {
                // Keycloak is not reachable: validate token manually
                /** @var UnencryptedToken $token */
                $token = $this->configuration->parser()->parse($accessToken);

                // Validate token
                $this->configuration->setValidationConstraints(
                    new LooseValidAt(new SystemClock(new \DateTimeZone(date_default_timezone_get()))), // Verifies the presence and validity of the claims iat, nbf, and exp
                    new SignedWith($this->configuration->signer(), $this->configuration->verificationKey()), // Verifies if the token was signed with the expected signer and key
                    new HasClaim('email') // Verifies that a custom claim exists
                );
                $this->configuration->validator()->assert($token, ...$this->configuration->validationConstraints());

                return $token->claims()->get('email');
            }
        } catch (\Throwable $e) {
            throw new BadCredentialsException('Invalid credentials.', $e->getCode(), $e);
        }
    }
}
