<?php

declare(strict_types=1);

namespace Qameta\Allure\PHPUnit\Test\Unit;

use Exception;
use PHPUnit\Framework\TestCase;
use Qameta\Allure\PHPUnit\ExceptionDetailsTrait;
use Qameta\Allure\PHPUnit\AllureAdapter;
use Qameta\Allure\PHPUnit\AllureAdapterInterface;
use Throwable;

/**
 * @covers \Qameta\Allure\PHPUnit\ExceptionDetailsTrait
 */
class ExceptionDetailsTraitTest extends TestCase
{
    public function testOnNotSuccessfulTest_GivenException_ThrowsSameException(): void
    {
        $sharedState = $this->createStub(AllureAdapterInterface::class);
        AllureAdapter::setInstance($sharedState);
        $object = new class () {
            use ExceptionDetailsTrait {
                ExceptionDetailsTrait::onNotSuccessfulTest as public;
            }
        };
        $error = new Exception('a', 1);
        $this->expectExceptionObject($error);
        $object->onNotSuccessfulTest($error);
    }

    public function testOnNotSuccessfulTest_GivenException_SetsSameExceptionAsLast(): void
    {
        $sharedState = $this->createMock(AllureAdapterInterface::class);
        AllureAdapter::setInstance($sharedState);
        $object = new class () {
            use ExceptionDetailsTrait {
                ExceptionDetailsTrait::onNotSuccessfulTest as public;
            }
        };
        $error = new Exception('a', 1);
        $sharedState
            ->expects(self::once())
            ->method('setLastException')
            ->with(self::identicalTo($error));
        try {
            $object->onNotSuccessfulTest($error);
        } catch (Throwable) {
        }
    }
}
