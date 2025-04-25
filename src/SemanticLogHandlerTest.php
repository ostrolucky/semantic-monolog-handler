<?php

namespace Ostrolucky\SemanticMonologHandler;

use Monolog\Handler\TestHandler;
use Monolog\Level;
use Monolog\LogRecord;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(SemanticLogHandler::class)]
class SemanticLogHandlerTest extends TestCase
{
    private TestHandler $testHandler;

    protected function setUp(): void
    {
        $this->testHandler = new TestHandler();
    }

    public function testHandle(): void
    {
        $handler = new SemanticLogHandler(
            $this->testHandler,
            [
                'app' => Level::Info,
                'all' => Level::Debug,
            ],
        );

        $date = new \DateTimeImmutable();
        // Not expected to activate the core logger yet
        $handler->handle(new LogRecord($date, 'app', Level::Debug, 'debug msg'));
        self::assertFalse($this->testHandler->hasDebugRecords());
        self::assertFalse($this->testHandler->hasInfoRecords());

        // "all" channel persists logs no matter the log level, because all messages are level >= DEBUG
        $handler->handle(new LogRecord($date, 'all', Level::Info, 'all info'));
        $handler->handle(new LogRecord($date, 'all', Level::Debug, 'all debug'));
        self::assertTrue($this->testHandler->hasDebugRecords());
        self::assertTrue($this->testHandler->hasInfoRecords());

        // Expected to persist log in inner logger for app channel only if level >= INFO
        $handler->handle(new LogRecord($date, 'app', Level::Info, 'info msg'));
        $handler->handle(new LogRecord($date, 'foo', Level::Info, 'non-app info'));

        self::assertTrue($this->testHandler->hasInfo('info msg'));
        self::assertFalse($this->testHandler->hasInfo('non-app info'));

        // Expected to persist everything (remaining) that was logged previously, because error activation happened
        $handler->handle(new LogRecord($date, 'app', Level::Warning, 'foo'));

        self::assertTrue($this->testHandler->hasDebug('debug msg'));
        self::assertTrue($this->testHandler->hasInfo('non-app info'));
        self::assertTrue($this->testHandler->hasWarning('foo'));
    }
}
