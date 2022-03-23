<?php

declare(strict_types=1);

namespace App\JWT\Validation\Constraint;

use Lcobucci\JWT\Token;
use Lcobucci\JWT\UnencryptedToken;
use Lcobucci\JWT\Validation\Constraint;
use Lcobucci\JWT\Validation\ConstraintViolation;

final class HasClaim implements Constraint
{
    public function __construct(private readonly string $claim)
    {
    }

    public function assert(Token $token): void
    {
        if (!$token instanceof UnencryptedToken) {
            throw new ConstraintViolation('You should pass a plain token');
        }

        if (!$token->claims()->has($this->claim)) {
            throw new ConstraintViolation(sprintf('The token does not have the claim "%s"', $this->claim));
        }
    }
}
