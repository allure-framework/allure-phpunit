<?php

declare(strict_types=1);

namespace Qameta\Allure\PHPUnit\Test\Unit;

// phpcs:disable PSR1.Classes.ClassDeclaration.MultipleClasses

use Exception;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversTrait;
use Qameta\Allure\PHPUnit\ExceptionDetailsTrait;
use Qameta\Allure\PHPUnit\AllureAdapter;
use Qameta\Allure\PHPUnit\AllureAdapterInterface;
use Throwable;

abstract class ExceptionDetailsTraitTestBase extends TestCase
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

/**
 * CoversTrait was added in PHPUnit 11.2.0.
 * If was then deprecated in 11.4.0 but the deprecation was reverted in 11.5.4.
 * @psalm-suppress DeprecatedClass
 */
if (class_exists(CoversTrait::class)) {
    #[CoversTrait(ExceptionDetailsTrait::class)]
    final class ExceptionDetailsTraitTest extends ExceptionDetailsTraitTestBase
    {
    }

} else {
    #[CoversClass(ExceptionDetailsTrait::class)]
    final class ExceptionDetailsTraitTest extends ExceptionDetailsTraitTestBase
    {
    }

}
