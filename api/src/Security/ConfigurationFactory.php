<?php

declare(strict_types=1);

namespace App\Security;

use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer;
use Lcobucci\JWT\Signer\Key\InMemory;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 *
 * @final
 */
class ConfigurationFactory
{
    /**
     * @var array<string, class-string<Signer>>
     */
    final public const SIGN_ALGORITHMS = [
        'HS256' => Signer\Hmac\Sha256::class,
        'HS384' => Signer\Hmac\Sha384::class,
        'HS512' => Signer\Hmac\Sha512::class,
        'ES256' => Signer\Ecdsa\Sha256::class,
        'ES384' => Signer\Ecdsa\Sha384::class,
        'ES512' => Signer\Ecdsa\Sha512::class,
        'RS256' => Signer\Rsa\Sha256::class,
        'RS384' => Signer\Rsa\Sha384::class,
        'RS512' => Signer\Rsa\Sha512::class,
    ];

    public static function createFromBase64Encoded(string $algorithm, string $contents): Configuration
    {
        $signerClass = self::SIGN_ALGORITHMS[$algorithm];

        return Configuration::forSymmetricSigner(new $signerClass(), InMemory::base64Encoded($contents));
    }

    public static function createFromFile(string $algorithm, string $path): Configuration
    {
        $signerClass = self::SIGN_ALGORITHMS[$algorithm];

        return Configuration::forSymmetricSigner(new $signerClass(), InMemory::file($path));
    }

    public static function createFromPlainText(string $algorithm, string $contents): Configuration
    {
        $signerClass = self::SIGN_ALGORITHMS[$algorithm];

        return Configuration::forSymmetricSigner(new $signerClass(), InMemory::plainText($contents));
    }

    public static function createFromUri(HttpClientInterface $oidcClient, string $algorithm): Configuration
    {
        $signerClass = self::SIGN_ALGORITHMS[$algorithm];

        return Configuration::forSymmetricSigner(new $signerClass(), InMemory::plainText(sprintf(<<<KEY
-----BEGIN PUBLIC KEY-----
%s
-----END PUBLIC KEY-----
KEY
            , $oidcClient->request('GET', '')->toArray()['public_key']
        )));
    }
}
