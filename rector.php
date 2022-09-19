<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Core\ValueObject\PhpVersion;
use Rector\Php74\Rector\Property\TypedPropertyRector;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;

if (!defined('DOCUMENT_ROOT')) {
    define('DOCUMENT_ROOT', __DIR__ . DIRECTORY_SEPARATOR);
}

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->importNames();

    $rectorConfig->parallel();

    $rectorConfig->paths([
        DOCUMENT_ROOT . 'wpAuth0.php',
        DOCUMENT_ROOT . 'functions.php',
        DOCUMENT_ROOT . 'src',
    ]);

    // Rector has challenges processing some classes in these folders; skip until we can find a better solution:
    $rectorConfig->skip([
        DOCUMENT_ROOT . 'src' . DIRECTORY_SEPARATOR . 'Http',
        DOCUMENT_ROOT . 'src' . DIRECTORY_SEPARATOR . 'Cache',
    ]);

    $rectorConfig->sets([
        SetList::ACTION_INJECTION_TO_CONSTRUCTOR_INJECTION,
        SetList::CODE_QUALITY,
        SetList::CODING_STYLE,
        SetList::DEAD_CODE,
        SetList::EARLY_RETURN,
        SetList::NAMING,
        SetList::PHP_80,
        SetList::PRIVATIZATION,
        SetList::PSR_4,
        SetList::TYPE_DECLARATION,
        SetList::TYPE_DECLARATION_STRICT,
        LevelSetList::UP_TO_PHP_80
    ]);

    $rectorConfig->rule(TypedPropertyRector::class);
    $rectorConfig->phpVersion(PhpVersion::PHP_80);
    $rectorConfig->phpstanConfig(DOCUMENT_ROOT . 'phpstan.neon.dist');
};
