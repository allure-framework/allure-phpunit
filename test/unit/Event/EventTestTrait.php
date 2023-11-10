<?php

declare(strict_types=1);

namespace Qameta\Allure\PHPUnit\Test\Unit\Event;

use Exception;
use PHPUnit\Event\Code\Test;
use PHPUnit\Event\Code\TestDox;
use PHPUnit\Event\Code\TestMethod;
use PHPUnit\Event\Code\Throwable;
use PHPUnit\Event\Telemetry\Duration;
use PHPUnit\Event\Telemetry\GarbageCollectorStatus;
use PHPUnit\Event\Telemetry\HRTime;
use PHPUnit\Event\Telemetry\Info;
use PHPUnit\Event\Telemetry\MemoryUsage;
use PHPUnit\Event\Telemetry\Snapshot;
use PHPUnit\Event\Test\ConsideredRisky;
use PHPUnit\Event\Test\Errored;
use PHPUnit\Event\Test\Failed;
use PHPUnit\Event\Test\Finished;
use PHPUnit\Event\Test\MarkedIncomplete;
use PHPUnit\Event\Test\Passed;
use PHPUnit\Event\Test\PreparationStarted;
use PHPUnit\Event\Test\Prepared;
use PHPUnit\Event\Test\Skipped;
use PHPUnit\Event\Test\WarningTriggered;
use PHPUnit\Event\TestData\TestDataCollection;
use PHPUnit\Metadata\MetadataCollection;

use function class_exists;
use function method_exists;

/**
 * Implementation-dependent helpers to test event subscribers.
 */
trait EventTestTrait
{
    protected function createTelemetryInfo(): Info
    {
        /**
         * @psalm-suppress MixedAssignment
         * @psalm-suppress TooManyArguments
         */
        $garbageCollectorStatus = class_exists(GarbageCollectorStatus::class)
            ? new GarbageCollectorStatus(
                0,
                0,
                0,
                0,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
            )
            : null; // early PHPUnit 10

        /**
         * @psalm-suppress TooManyArguments
         * @psalm-suppress PossiblyNullArgument
         */
        $snapshot = new Snapshot(
            HRTime::fromSecondsAndNanoseconds(0, 0),
            MemoryUsage::fromBytes(0),
            MemoryUsage::fromBytes(0),
            $garbageCollectorStatus,
        );

        return new Info(
            $snapshot,
            Duration::fromSecondsAndNanoseconds(0, 0),
            MemoryUsage::fromBytes(0),
            Duration::fromSecondsAndNanoseconds(0, 0),
            MemoryUsage::fromBytes(0),
        );
    }

    protected function createTestMethod(
        ?string $class = null,
        ?string $methodName = null,
        ?string $file = null,
        ?int $line = null,
    ): TestMethod {
        $class ??= 'class';
        $methodName ??= 'method';
        $file ??= 'file';
        $line ??= 0;

        /**
         * @var class-string $class
         * @var non-empty-string $methodName
         * @var TestDox $testDox
         * @psalm-suppress InaccessibleMethod
         */
        $testDox = method_exists(TestDox::class, 'fromClassNameAndMethodName')
            ? TestDox::fromClassNameAndMethodName($class, $methodName) // early PHPUnit 10
            : new TestDox(
                'pretty class',
                'pretty method',
                'pretty colorized method',
            );

        /**
         * @var non-empty-string $file
         * @var int<0, max> $line
         * @psalm-suppress InternalClass
         * @psalm-suppress InternalMethod
         */
        return  new TestMethod(
            $class,
            $methodName,
            $file,
            $line,
            $testDox,
            MetadataCollection::fromArray([]),
            TestDataCollection::fromArray([]),
        );
    }

    protected function createThrowable(
        ?string $message = null,
        ?string $description = null,
    ): Throwable {
        $throwable = new Exception($message ?? 'message');

        /**
         * @var Throwable
         * @psalm-suppress InaccessibleMethod
         */
        return method_exists(Throwable::class, 'from')
            ? Throwable::from($throwable) // early PHPUnit 10
            : new Throwable(
                $throwable::class,
                $throwable->getMessage(),
                $description ?? 'description',
                $throwable->getTraceAsString(),
                null,
            );
    }

    protected function createTestConsideredRiskyEvent(
        Test $test,
        ?string $message = null,
    ): ConsideredRisky {
        $message ??= 'message';

        /** @var non-empty-string $message */
        return new ConsideredRisky(
            $this->createTelemetryInfo(),
            $test,
            $message,
        );
    }

    protected function createTestErroredEvent(
        Test $test,
        ?string $message = null,
    ): Errored {
        return new Errored(
            $this->createTelemetryInfo(),
            $test,
            $this->createThrowable(
                message: $message ?? 'message',
            ),
        );
    }

    protected function createTestFailedEvent(
        Test $test,
        ?string $message = null,
    ): Failed {
        return new Failed(
            $this->createTelemetryInfo(),
            $test,
            $this->createThrowable(
                message: $message ?? 'message',
            ),
            null,
        );
    }

    protected function createTestFinishedEvent(
        Test $test,
    ): Finished {
        return new Finished(
            $this->createTelemetryInfo(),
            $test,
            0,
        );
    }

    protected function createTestMarkedIncompleteEvent(
        Test $test,
        ?string $message = null,
    ): MarkedIncomplete {
        return new MarkedIncomplete(
            $this->createTelemetryInfo(),
            $test,
            $this->createThrowable(
                message: $message ?? 'message',
            ),
        );
    }

    protected function createTestPassesEvent(
        Test $test,
    ): Passed {
        return new Passed(
            $this->createTelemetryInfo(),
            $test,
        );
    }

    protected function createTestPreparationStartedEvent(
        Test $test,
    ): PreparationStarted {
        return new PreparationStarted(
            $this->createTelemetryInfo(),
            $test,
        );
    }

    protected function createTestPreparedEvent(
        Test $test,
    ): Prepared {
        return new Prepared(
            $this->createTelemetryInfo(),
            $test,
        );
    }

    protected function createTestSkippedEvent(
        Test $test,
        ?string $message = null,
    ): Skipped {
        return new Skipped(
            $this->createTelemetryInfo(),
            $test,
            $message ?? 'message',
        );
    }

    protected function createTestWarningTriggeredEvent(
        Test $test,
        ?string $message = null,
    ): WarningTriggered {
        $message ??= 'message';

        /**
         * @var non-empty-string $message
         * @psalm-suppress TooManyArguments
         */
        return new WarningTriggered(
            $this->createTelemetryInfo(),
            $test,
            $message,
            'file',
            1,
            false,
            false,
        );
    }
}
