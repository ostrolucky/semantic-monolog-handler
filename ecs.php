<?php

declare(strict_types=1);

use Symplify\EasyCodingStandard\Config\ECSConfig;

return ECSConfig::configure()
    ->withPaths([__DIR__ . '/src'])
    ->withPhpCsFixerSets(symfony: true)
    ->withPreparedSets(symplify: true)
 ;
