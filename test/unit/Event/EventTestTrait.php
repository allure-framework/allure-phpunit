<?php

declare(strict_types=1);

namespace Qameta\Allure\PHPUnit\Test\Unit\Event;

use Exception;
use PHPUnit\Event\Code\Test;
use PHPUnit\Event\Code\TestDox;
use PHPUnit\Event\Code\TestMethod;
use PHPUnit\Event\Code\Throwable;
use PHPUnit\Event\Code\ClassMethod;
use PHPUnit\Event\Telemetry\Duration;
use PHPUnit\Event\Telemetry\GarbageCollectorStatus;
use PHPUnit\Event\Telemetry\HRTime;
use PHPUnit\Event\Telemetry\Info;
use PHPUnit\Event\Telemetry\MemoryUsage;
use PHPUnit\Event\Telemetry\Snapshot;
use PHPUnit\Event\Test\BeforeFirstTestMethodCalled;
use PHPUnit\Event\Test\BeforeFirstTestMethodErrored;
use PHPUnit\Event\Test\BeforeTestMethodCalled;
use PHPUnit\Event\Test\BeforeTestMethodErrored;
use PHPUnit\Event\Test\BeforeTestMethodFailed;
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
 *
 * @psalm-suppress InternalMethod
 */
trait EventTestTrait
{
    protected function createTelemetryInfo(): Info
    {
        $garbageCollectorStatus = self::createGarbageCollectorStatus();

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

    /**
     * @psalm-suppress InternalMethod
     * @psalm-suppress ArgumentTypeCoercion
     */
    protected function createClassMethod(
        ?string $className = null,
        ?string $methodName = null,
    ): ClassMethod {
        /**
         * @var class-string $class
         * @var non-empty-string $method
         */
        $class = $className ?? 'class';
        $method = $methodName ?? 'setUp';

        return new ClassMethod($class, $method);
    }

    /**
     * PHPUnit < 12.5: (Info, class-string, ClassMethod)
     * PHPUnit >= 12.5: (Info, TestMethod, ClassMethod)
     *
     * @psalm-suppress InternalMethod
     * @psalm-suppress ArgumentTypeCoercion
     * @psalm-suppress TooManyArguments
     * @psalm-suppress InvalidArgument
     */
    protected function createBeforeTestMethodCalledEvent(
        TestMethod $test,
        ?string $hookName = null,
    ): BeforeTestMethodCalled {
        $calledMethod = $this->createClassMethod($test->className(), $hookName ?? 'setUp');
        if (method_exists(BeforeTestMethodCalled::class, 'test')) {
            return new BeforeTestMethodCalled(
                $this->createTelemetryInfo(),
                $test,
                $calledMethod,
            );
        }

        return new BeforeTestMethodCalled(
            $this->createTelemetryInfo(),
            $test->className(),
            $calledMethod,
        );
    }

    /**
     * @psalm-suppress InternalMethod
     * @psalm-suppress ArgumentTypeCoercion
     * @psalm-suppress TooManyArguments
     * @psalm-suppress InvalidArgument
     * @psalm-suppress UndefinedClass
     */
    protected function createBeforeTestMethodFailedEvent(
        TestMethod $test,
        ?string $message = null,
        ?string $hookName = null,
    ): BeforeTestMethodFailed {
        $calledMethod = $this->createClassMethod($test->className(), $hookName ?? 'setUp');
        $throwable = $this->createThrowable(message: $message ?? 'message');
        if (method_exists(BeforeTestMethodFailed::class, 'test')) {
            return new BeforeTestMethodFailed(
                $this->createTelemetryInfo(),
                $test,
                $calledMethod,
                $throwable,
            );
        }

        return new BeforeTestMethodFailed(
            $this->createTelemetryInfo(),
            $test->className(),
            $calledMethod,
            $throwable,
        );
    }

    /**
     * PHPUnit < 12.5: (Info, class-string, ClassMethod, Throwable)
     * PHPUnit >= 12.5: (Info, TestMethod, ClassMethod, Throwable)
     *
     * @psalm-suppress InternalMethod
     * @psalm-suppress ArgumentTypeCoercion
     * @psalm-suppress TooManyArguments
     * @psalm-suppress InvalidArgument
     */
    protected function createBeforeTestMethodErroredEvent(
        TestMethod $test,
        ?string $message = null,
        ?string $hookName = null,
    ): BeforeTestMethodErrored {
        $calledMethod = $this->createClassMethod($test->className(), $hookName ?? 'setUp');
        $throwable = $this->createThrowable(message: $message ?? 'message');
        if (method_exists(BeforeTestMethodErrored::class, 'test')) {
            return new BeforeTestMethodErrored(
                $this->createTelemetryInfo(),
                $test,
                $calledMethod,
                $throwable,
            );
        }

        return new BeforeTestMethodErrored(
            $this->createTelemetryInfo(),
            $test->className(),
            $calledMethod,
            $throwable,
        );
    }

    /**
     * @param class-string $testClassName
     * @psalm-suppress InternalMethod
     * @psalm-suppress ArgumentTypeCoercion
     */
    protected function createBeforeFirstTestMethodCalledEvent(
        string $testClassName,
        ?string $hookName = null,
    ): BeforeFirstTestMethodCalled {
        return new BeforeFirstTestMethodCalled(
            $this->createTelemetryInfo(),
            $testClassName,
            $this->createClassMethod($testClassName, $hookName ?? 'setUpBeforeClass'),
        );
    }

    /**
     * @param class-string $testClassName
     * @psalm-suppress InternalMethod
     * @psalm-suppress ArgumentTypeCoercion
     */
    protected function createBeforeFirstTestMethodErroredEvent(
        string $testClassName,
        ?string $message = null,
        ?string $hookName = null,
    ): BeforeFirstTestMethodErrored {
        return new BeforeFirstTestMethodErrored(
            $this->createTelemetryInfo(),
            $testClassName,
            $this->createClassMethod($testClassName, $hookName ?? 'setUpBeforeClass'),
            $this->createThrowable(message: $message ?? 'message'),
        );
    }

    /**
     * GarbageCollectorStatus doesn't exist in PHPUnit 10.0
     * @psalm-suppress UndefinedClass
     */
    private static function createGarbageCollectorStatus(): ?GarbageCollectorStatus
    {
        // GarbageCollectorStatus doesn't exist in PHPUnit 10.0
        if (class_exists(GarbageCollectorStatus::class)) {
            $ctorInfo = (new \ReflectionClass(GarbageCollectorStatus::class))->getConstructor();
            if (isset($ctorInfo)) {
                /**
                 * @psalm-suppress InvalidArgument
                 * @psalm-suppress TooFewArguments
                 * @psalm-suppress TooManyArguments
                 */
                return $ctorInfo->getNumberOfParameters() === 8
                    # PHPUnit [10.1, 10.3)
                    ? new GarbageCollectorStatus(
                        0,
                        0,
                        0,
                        0,
                        false,
                        false,
                        false,
                        0,
                    )
                    # Extra arguments are required since 10.3.0
                    : new GarbageCollectorStatus(
                        0,
                        0,
                        0,
                        0,
                        0.0,
                        0.0,
                        0.0,
                        0.0,
                        false,
                        false,
                        false,
                        0,
                    );
            }
        }
        return null;
    }
}
