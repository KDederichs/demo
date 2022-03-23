<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Core\ValueObject\PhpVersion;
use Rector\PHPUnit\Rector\Class_\AddSeeTestAnnotationRector;
use Rector\PHPUnit\Set\PHPUnitSetList;
use Rector\Set\ValueObject\SetList;
use Rector\Symfony\Set\SymfonySetList;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->paths([__DIR__.'/src', __DIR__.'/tests']);
    $rectorConfig->phpVersion(PhpVersion::PHP_81);
    $rectorConfig->importNames();

    // Generic rule
    $rectorConfig->sets([
        // Fatal Error on this set (Rector\Php72\NodeFactory\AnonymousFunctionFactory)
//        SetList::CODE_QUALITY,
        SetList::CODING_STYLE,
        SetList::DEAD_CODE,
        SetList::PHP_81,
        SetList::PSR_4,
        SetList::TYPE_DECLARATION_STRICT,
        SetList::EARLY_RETURN,
    ]);

    // Symfony
    $rectorConfig->symfonyContainerXml(__DIR__.'/var/cache/dev/App_KernelDevDebugContainer.xml');
    $rectorConfig->sets([
        SymfonySetList::ANNOTATIONS_TO_ATTRIBUTES,
        SymfonySetList::SYMFONY_60,
        SymfonySetList::SYMFONY_CODE_QUALITY,
        SymfonySetList::SYMFONY_CONSTRUCTOR_INJECTION,
        SymfonySetList::SYMFONY_STRICT,
    ]);

    // PHPUnit
    $rectorConfig->sets([
        PHPUnitSetList::PHPUNIT_91,
        PHPUnitSetList::PHPUNIT_CODE_QUALITY,
        PHPUnitSetList::PHPUNIT_EXCEPTION,
        PHPUnitSetList::PHPUNIT_MOCK,
        PHPUnitSetList::PHPUNIT_SPECIFIC_METHOD,
        PHPUnitSetList::PHPUNIT_YIELD_DATA_PROVIDER,
    ]);
    $rectorConfig->skip([AddSeeTestAnnotationRector::class]);
};
