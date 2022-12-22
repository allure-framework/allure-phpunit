<?php

declare(strict_types=1);

namespace Qameta\Allure\PHPUnit\Test\Unit;

use Exception;
use LogicException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Qameta\Allure\Model\ContainerResult;
use Qameta\Allure\Model\Parameter;
use Qameta\Allure\Model\TestResult;
use Qameta\Allure\PHPUnit\AllureAdapter;
use Qameta\Allure\PHPUnit\AllureAdapterInterface;
use Qameta\Allure\PHPUnit\Internal\TestInfo;
use stdClass;

use function array_keys;
use function array_map;

#[CoversClass(AllureAdapter::class)]
class AllureAdapterTest extends TestCase
{
    public function setUp(): void
    {
        AllureAdapter::reset();
    }

    public function testGetInstance_CalledTwiceWithoutReset_ReturnsSameInstance(): void
    {
        $adapter = AllureAdapter::getInstance();
        self::assertSame($adapter, AllureAdapter::getInstance());
    }

    public function testGetInstance_CalledTwiceWithReset_ReturnsNewInstance(): void
    {
        $adapter = AllureAdapter::getInstance();
        AllureAdapter::reset();
        self::assertNotSame($adapter, AllureAdapter::getInstance());
    }

    public function testGetInstance_InstanceIsSet_ReturnsSameInstance(): void
    {
        $adapter = $this->createStub(AllureAdapterInterface::class);
        AllureAdapter::setInstance($adapter);
        self::assertSame($adapter, AllureAdapter::getInstance());
    }

    public function testGetLastException_LastExceptionNotSet_ReturnsNull(): void
    {
        $adapter = AllureAdapter::getInstance();
        self::assertNull($adapter->getLastException());
    }

    public function testGetLastException_LastExceptionSet_ReturnsSameException(): void
    {
        $adapter = AllureAdapter::getInstance();
        $error = new Exception();
        $adapter->setLastException($error);
        self::assertSame($error, $adapter->getLastException());
    }

    public function testGetLastException_LastExceptionSetAndReset_ReturnsSameException(): void
    {
        $adapter = AllureAdapter::getInstance();
        $error = new Exception();
        $adapter->setLastException($error);
        $adapter->resetLastException();
        self::assertNull($adapter->getLastException());
    }

    public function testGetContainerId_StartNotRegistered_ThrowsException(): void
    {
        $adapter = AllureAdapter::getInstance();
        $info = new TestInfo(
            test: 'a',
            class: null,
            method: null,
            dataLabel: null,
            host: null,
            thread: null,
        );
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Container not registered: a');
        $adapter->getContainerId($info);
    }

    public function testGetContainerId_StartRegisteredWithContainer_ReturnsContainerId(): void
    {
        $adapter = AllureAdapter::getInstance();
        $info = new TestInfo(
            test: 'a',
            class: null,
            method: null,
            dataLabel: null,
            host: null,
            thread: null,
        );
        $adapter->registerStart(new ContainerResult('a'), new TestResult('b'), $info);
        self::assertSame('a', $adapter->getContainerId($info));
    }

    public function testGetTestId_StartNotRegistered_ThrowsException(): void
    {
        $adapter = AllureAdapter::getInstance();
        $info = new TestInfo(
            test: 'a',
            class: null,
            method: null,
            dataLabel: null,
            host: null,
            thread: null,
        );
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Test not registered: a');
        $adapter->getTestId($info);
    }

    public function testGetTestId_StartRegisteredWithTest_ReturnsTestId(): void
    {
        $adapter = AllureAdapter::getInstance();
        $info = new TestInfo(
            test: 'a',
            class: null,
            method: null,
            dataLabel: null,
            host: null,
            thread: null,
        );
        $adapter->registerStart(new ContainerResult('a'), new TestResult('b'), $info);
        self::assertSame('b', $adapter->getTestId($info));
    }

    public function testRegisterRun_GivenInfo_ResultHasSameInfo(): void
    {
        $adapter = AllureAdapter::getInstance();
        $info = new TestInfo(
            test: 'a',
            class: null,
            method: null,
            dataLabel: null,
            host: null,
            thread: null,
        );
        $runInfo = $adapter->registerRun(new TestResult('b'), $info);
        self::assertSame($info, $runInfo->getTestInfo());
    }

    public function testRegisterRun_TestWithGivenUuid_ResultHasSameUuid(): void
    {
        $adapter = AllureAdapter::getInstance();
        $info = new TestInfo(
            test: 'a',
            class: null,
            method: null,
            dataLabel: null,
            host: null,
            thread: null,
        );
        $runInfo = $adapter->registerRun(new TestResult('b'), $info);
        self::assertSame('b', $runInfo->getUuid());
    }

    public function testRegisterRun_NoTestsRegisteredBefore_ResultHasZeroRunIndex(): void
    {
        $adapter = AllureAdapter::getInstance();
        $info = new TestInfo(
            test: 'a',
            class: null,
            method: null,
            dataLabel: null,
            host: null,
            thread: null,
        );
        $runInfo = $adapter->registerRun(new TestResult('b'), $info);
        self::assertSame(0, $runInfo->getRunIndex());
    }

    public function testRegisterRun_NoTestsRegisteredBefore_ResultHasNullRerunOf(): void
    {
        $adapter = AllureAdapter::getInstance();
        $info = new TestInfo(
            test: 'a',
            class: null,
            method: null,
            dataLabel: null,
            host: null,
            thread: null,
        );
        $runInfo = $adapter->registerRun(new TestResult('b'), $info);
        self::assertNull($runInfo->getRerunOf());
    }

    /**
     * @param string                     $firstTest
     * @param class-string|null          $firstClass
     * @param string|null                $firstMethod
     * @param array<string, string|null> $firstParameters
     * @param string                     $secondTest
     * @param class-string|null          $secondClass
     * @param string|null                $secondMethod
     * @param array<string, string|null> $secondParameters
     * @param int                        $expectedRunIndex
     */
    #[DataProvider('providerRegisterRunRunIndex')]
    public function testRegisterRun_TestRegisteredBefore_ResultHasMatchingRunIndex(
        string $firstTest,
        ?string $firstClass,
        ?string $firstMethod,
        array $firstParameters,
        string $secondTest,
        ?string $secondClass,
        ?string $secondMethod,
        array $secondParameters,
        int $expectedRunIndex,
    ): void {
        $adapter = AllureAdapter::getInstance();
        $firstInfo = new TestInfo(
            test: $firstTest,
            class: $firstClass,
            method: $firstMethod,
            dataLabel: null,
            host: null,
            thread: null,
        );
        $firstTestResult = new TestResult('y');
        $firstTestResult->addParameters(...$this->createParameters($firstParameters));
        $secondInfo = new TestInfo(
            test: $secondTest,
            class: $secondClass,
            method: $secondMethod,
            dataLabel: null,
            host: null,
            thread: null,
        );
        $secondTestResult = new TestResult('z');
        $secondTestResult->addParameters(...$this->createParameters($secondParameters));
        $adapter->registerRun($firstTestResult, $firstInfo);
        $runInfo = $adapter->registerRun($secondTestResult, $secondInfo);
        self::assertSame($expectedRunIndex, $runInfo->getRunIndex());
    }

    /**
     * @return iterable<string, array{
     *      string,
     *      class-string|null,
     *      string|null,
     *      array<string, string|null>,
     *      string,
     *      class-string|null,
     *      string|null,
     *      array<string, string|null>,
     *      int
     * }>
     */
    public static function providerRegisterRunRunIndex(): iterable
    {
        return [
            'Same test and no parameters' => [
                'a',
                null,
                null,
                [],
                'a',
                null,
                null,
                [],
                1,
            ],
            'Same test and parameters' => [
                'a',
                null,
                null,
                ['b' => 'c', 'd' => null],
                'a',
                null,
                null,
                ['b' => 'c', 'd' => null],
                1,
            ],
            'Same full name and parameters' => [
                'a',
                stdClass::class,
                'c',
                ['d' => 'e', 'f' => null],
                'g',
                stdClass::class,
                'c',
                ['d' => 'e', 'f' => null],
                1,
            ],
            'Same test and different parameter names' => [
                'a',
                null,
                null,
                ['b' => 'c', 'd' => null],
                'a',
                null,
                null,
                ['e' => 'c', 'f' => null],
                0,
            ],
            'Same test and different parameter values' => [
                'a',
                null,
                null,
                ['b' => 'c', 'd' => null],
                'a',
                null,
                null,
                ['b' => 'e', 'd' => 'f'],
                0,
            ],
        ];
    }

    /**
     * @param string                     $firstTest
     * @param class-string|null          $firstClass
     * @param string|null                $firstMethod
     * @param array<string, string|null> $firstParameters
     * @param string                     $firstUuid
     * @param string                     $secondTest
     * @param class-string|null          $secondClass
     * @param string|null                $secondMethod
     * @param array<string, string|null> $secondParameters
     * @param string                     $secondUuid
     * @param string|null                $expectedRerunOf
     */
    #[DataProvider('providerRegisterRunRerunOf')]
    public function testRegisterRun_TestRegisteredBefore_ResultHasMatchingRerunOf(
        string $firstTest,
        ?string $firstClass,
        ?string $firstMethod,
        array $firstParameters,
        string $firstUuid,
        string $secondTest,
        ?string $secondClass,
        ?string $secondMethod,
        array $secondParameters,
        string $secondUuid,
        ?string $expectedRerunOf,
    ): void {
        $adapter = AllureAdapter::getInstance();
        $firstInfo = new TestInfo(
            test: $firstTest,
            class: $firstClass,
            method: $firstMethod,
            dataLabel: null,
            host: null,
            thread: null,
        );
        $firstTestResult = new TestResult($firstUuid);
        $firstTestResult->addParameters(...$this->createParameters($firstParameters));
        $secondInfo = new TestInfo(
            test: $secondTest,
            class: $secondClass,
            method: $secondMethod,
            dataLabel: null,
            host: null,
            thread: null,
        );
        $secondTestResult = new TestResult($secondUuid);
        $secondTestResult->addParameters(...$this->createParameters($secondParameters));
        $adapter->registerRun($firstTestResult, $firstInfo);
        $runInfo = $adapter->registerRun($secondTestResult, $secondInfo);
        self::assertSame($expectedRerunOf, $runInfo->getRerunOf());
    }

    /**
     * @return iterable<string, array{
     *      string,
     *      class-string|null,
     *      string|null,
     *      array<string, string|null>,
     *      string,
     *      string,
     *      class-string|null,
     *      string|null,
     *      array<string, string|null>,
     *      string,
     *      string|null
     * }>
     */
    public static function providerRegisterRunRerunOf(): iterable
    {
        return [
            'Same test and no parameters' => [
                'a',
                null,
                null,
                [],
                'b',
                'a',
                null,
                null,
                [],
                'c',
                'b',
            ],
            'Same test and parameters' => [
                'a',
                null,
                null,
                ['b' => 'c', 'd' => null],
                'e',
                'a',
                null,
                null,
                ['b' => 'c', 'd' => null],
                'f',
                'e',
            ],
            'Same full name and parameters' => [
                'a',
                stdClass::class,
                'c',
                ['d' => 'e', 'f' => null],
                'g',
                'h',
                stdClass::class,
                'c',
                ['d' => 'e', 'f' => null],
                'i',
                'g',
            ],
            'Same test and different parameter names' => [
                'a',
                null,
                null,
                ['b' => 'c', 'd' => null],
                'e',
                'a',
                null,
                null,
                ['f' => 'c', 'g' => null],
                'h',
                null,
            ],
            'Same test and different parameter values' => [
                'a',
                null,
                null,
                ['b' => 'c', 'd' => null],
                'e',
                'a',
                null,
                null,
                ['b' => 'f', 'd' => 'g'],
                'h',
                null,
            ],
        ];
    }

    /**
     * @param string                     $firstTest
     * @param class-string|null          $firstClass
     * @param string|null                $firstMethod
     * @param array<string, string|null> $firstParameters
     * @param string                     $firstUuid
     * @param string                     $secondTest
     * @param class-string|null          $secondClass
     * @param string|null                $secondMethod
     * @param array<string, string|null> $secondParameters
     * @param string                     $secondUuid
     */
    #[DataProvider('providerRegisterRunSameTestCaseId')]
    public function testRegisterRun_MatchingTestRegisteredWithGivenTestCaseId_ResultHasSameTestCaseId(
        string $firstTest,
        ?string $firstClass,
        ?string $firstMethod,
        array $firstParameters,
        string $firstUuid,
        string $secondTest,
        ?string $secondClass,
        ?string $secondMethod,
        array $secondParameters,
        string $secondUuid,
    ): void {
        $adapter = AllureAdapter::getInstance();
        $firstInfo = new TestInfo(
            test: $firstTest,
            class: $firstClass,
            method: $firstMethod,
            dataLabel: null,
            host: null,
            thread: null,
        );
        $firstTestResult = new TestResult($firstUuid);
        $firstTestResult->addParameters(...$this->createParameters($firstParameters));
        $secondInfo = new TestInfo(
            test: $secondTest,
            class: $secondClass,
            method: $secondMethod,
            dataLabel: null,
            host: null,
            thread: null,
        );
        $secondTestResult = new TestResult($secondUuid);
        $secondTestResult->addParameters(...$this->createParameters($secondParameters));
        $firstRunInfo = $adapter->registerRun($firstTestResult, $firstInfo);
        $secondRunInfo = $adapter->registerRun($secondTestResult, $secondInfo);
        self::assertSame($firstRunInfo->getTestCaseId(), $secondRunInfo->getTestCaseId());
    }

    /**
     * @return iterable<string, array{
     *      string,
     *      class-string|null,
     *      string|null,
     *      array<string, string|null>,
     *      string,
     *      string,
     *      class-string|null,
     *      string|null,
     *      array<string, string|null>,
     *      string
     * }>
     */
    public static function providerRegisterRunSameTestCaseId(): iterable
    {
        return [
            'Same test and no parameters' => [
                'a',
                null,
                null,
                [],
                'b',
                'a',
                null,
                null,
                [],
                'c',
            ],
            'Same test and parameters' => [
                'a',
                null,
                null,
                ['b' => 'c', 'd' => null],
                'e',
                'a',
                null,
                null,
                ['b' => 'c', 'd' => null],
                'f',
            ],
            'Same full name and parameters' => [
                'a',
                stdClass::class,
                'c',
                ['d' => 'e', 'f' => null],
                'g',
                'h',
                stdClass::class,
                'c',
                ['d' => 'e', 'f' => null],
                'i',
            ],
            'Same test and different parameter values' => [
                'a',
                null,
                null,
                ['b' => 'c', 'd' => null],
                'e',
                'a',
                null,
                null,
                ['b' => 'f', 'd' => 'g'],
                'h',
            ],
        ];
    }

    /**
     * @param string                     $firstTest
     * @param class-string|null          $firstClass
     * @param string|null                $firstMethod
     * @param array<string, string|null> $firstParameters
     * @param string                     $firstUuid
     * @param string                     $secondTest
     * @param class-string|null          $secondClass
     * @param string|null                $secondMethod
     * @param array<string, string|null> $secondParameters
     * @param string                     $secondUuid
     */
    #[DataProvider('providerRegisterRunNewTestCaseId')]
    public function testRegisterRun_NonMatchingTestRegisteredWithGivenTestCaseId_ResultHasNewTestCaseId(
        string $firstTest,
        ?string $firstClass,
        ?string $firstMethod,
        array $firstParameters,
        string $firstUuid,
        string $secondTest,
        ?string $secondClass,
        ?string $secondMethod,
        array $secondParameters,
        string $secondUuid,
    ): void {
        $adapter = AllureAdapter::getInstance();
        $firstInfo = new TestInfo(
            test: $firstTest,
            class: $firstClass,
            method: $firstMethod,
            dataLabel: null,
            host: null,
            thread: null,
        );
        $firstTestResult = new TestResult($firstUuid);
        $firstTestResult->addParameters(...$this->createParameters($firstParameters));
        $secondInfo = new TestInfo(
            test: $secondTest,
            class: $secondClass,
            method: $secondMethod,
            dataLabel: null,
            host: null,
            thread: null,
        );
        $secondTestResult = new TestResult($secondUuid);
        $secondTestResult->addParameters(...$this->createParameters($secondParameters));
        $firstRunInfo = $adapter->registerRun($firstTestResult, $firstInfo);
        $secondRunInfo = $adapter->registerRun($secondTestResult, $secondInfo);
        self::assertNotSame($firstRunInfo->getTestCaseId(), $secondRunInfo->getTestCaseId());
    }

    /**
     * @return iterable<string, array{
     *      string,
     *      class-string|null,
     *      string|null,
     *      array<string, string|null>,
     *      string,
     *      string,
     *      class-string|null,
     *      string|null,
     *      array<string, string|null>,
     *      string
     * }>
     */
    public static function providerRegisterRunNewTestCaseId(): iterable
    {
        return [
            'Same test and different parameter names' => [
                'a',
                null,
                null,
                ['b' => 'c', 'd' => null],
                'e',
                'a',
                null,
                null,
                ['f' => 'c', 'g' => null],
                'h',
            ],
            'Same full name and different parameter names' => [
                'a',
                stdClass::class,
                'c',
                ['d' => 'e', 'f' => null],
                'g',
                'h',
                stdClass::class,
                'c',
                ['i' => 'c', 'j' => null],
                'k',
            ],
            'Different class and same parameters' => [
                'a',
                stdClass::class,
                'c',
                ['d' => 'e', 'f' => null],
                'g',
                'h',
                self::class,
                'c',
                ['d' => 'e', 'f' => null],
                'j',
            ],
            'Different method and same parameters' => [
                'a',
                stdClass::class,
                'c',
                ['d' => 'e', 'f' => null],
                'g',
                'h',
                stdClass::class,
                'i',
                ['d' => 'e', 'f' => null],
                'j',
            ],
            'Different test and same parameters' => [
                'a',
                null,
                null,
                ['b' => 'c', 'd' => null],
                'e',
                'f',
                null,
                null,
                ['b' => 'c', 'd' => null],
                'g',
            ],
        ];
    }

    /**
     * @param array<string, string|null> $parameters
     * @return list<Parameter>
     */
    private function createParameters(array $parameters): array
    {
        return array_map(
            fn(string $name, ?string $value) => new Parameter($name, $value),
            array_keys($parameters),
            $parameters,
        );
    }
}
