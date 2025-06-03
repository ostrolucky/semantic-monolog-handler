<?php

declare(strict_types=1);

namespace Ostrolucky\SemanticMonologHandler;

use Monolog\Handler\FingersCrossed\ActivationStrategyInterface;
use Monolog\Handler\FingersCrossed\ErrorLevelActivationStrategy;
use Monolog\Handler\FingersCrossedHandler;
use Monolog\Handler\HandlerInterface;
use Monolog\Level;
use Monolog\LogRecord;

class SemanticLogHandler extends FingersCrossedHandler
{
    /** @param array<string, Level> $primaryChannels */
    public function __construct(
        private HandlerInterface $innerHandler,
        private array $primaryChannels = [
            'app' => Level::Info,
        ],
        ActivationStrategyInterface $activationStrategy = new ErrorLevelActivationStrategy(Level::Warning),
        int $bufferSize = 0,
    ) {
        parent::__construct($innerHandler, $activationStrategy, $bufferSize);
    }

    public function handle(LogRecord $record): bool
    {
        if (!$customActivationLevel = $this->primaryChannels[$record->channel] ?? null) {
            return parent::handle($record);
        }

        if ($this->activationStrategy->isHandlerActivated($record)) {
            return parent::handle($record);
        }

        if ($record->level->isLowerThan($customActivationLevel)) {
            return parent::handle($record);
        }

        return $this->innerHandler->handle($record);
    }
}
