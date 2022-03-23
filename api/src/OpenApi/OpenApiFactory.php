<?php

declare(strict_types=1);

namespace App\OpenApi;

use ApiPlatform\OpenApi\Factory\OpenApiFactoryInterface;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\PathItem;
use ApiPlatform\OpenApi\OpenApi;
use Symfony\Component\DependencyInjection\Attribute\AsDecorator;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
#[AsDecorator(decorates: 'api_platform.openapi.factory')]
final class OpenApiFactory implements OpenApiFactoryInterface
{
    public function __construct(private readonly OpenApiFactoryInterface $decorated)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke(array $context = []): OpenApi
    {
        $openApi = ($this->decorated)($context);
        $paths = $openApi->getPaths();
        $paths->addPath('/stats', (new PathItem())->withGet((new Operation())
            ->withOperationId('get')
            ->withTags(['Stats'])
            ->withResponses([
                Response::HTTP_OK => [
                    'content' => [
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'properties' => [
                                    'books_count' => [
                                        'type' => 'integer',
                                        'example' => 997,
                                    ],
                                    'topbooks_count' => [
                                        'type' => 'integer',
                                        'example' => 101,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ])
            ->withSummary('Retrieves the number of books and top books (legacy endpoint).')
        ));
        $paths->addPath('/profile', (new PathItem())->withGet((new Operation())
            ->withOperationId('get')
            ->withTags(['Profile'])
            ->withResponses([
                Response::HTTP_OK => [
                    'content' => [
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'properties' => [
                                    'id' => ['type' => 'string'],
                                    'email' => ['type' => 'string'],
                                    'roles' => ['type' => 'array'],
                                ],
                            ],
                        ],
                    ],
                ],
            ])
            ->withSummary('Show the current user profile.')
            ->withDescription('You can authenticate on Keycloak "user:Pa55w0rd" credentials.')
        ));

        return $openApi;
    }
}
