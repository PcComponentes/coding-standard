<?php
declare(strict_types=1);

namespace PcComponentesCodingStandard\Sniffs\Namespaces;

use SlevomatCodingStandard\Sniffs\TestCase;

class RequireNamespaceInNativeFunctionSniffTest extends TestCase
{
    public function testNoErrors(): void
    {
        $report = self::checkFile(__DIR__ . '/data/requireNamespaceInNativeFunctionErrors.fixed.php');
        self::assertNoSniffErrorInFile($report);
    }

    public function testErrors(): void
    {
        $report = self::checkFile(__DIR__ . '/data/requireNamespaceInNativeFunctionErrors.php');
        self::assertSame(4, $report->getErrorCount());
        self::assertSniffError($report, 7, RequireNamespaceInNativeFunctionSniff::CODE_REQUIRE_NAMESPACE_IN_NATIVE_FUNCTION);
        self::assertSniffError($report, 8, RequireNamespaceInNativeFunctionSniff::CODE_REQUIRE_NAMESPACE_IN_NATIVE_FUNCTION);
        self::assertSniffError($report, 10, RequireNamespaceInNativeFunctionSniff::CODE_REQUIRE_NAMESPACE_IN_NATIVE_FUNCTION);
        self::assertAllFixedInFile($report);
    }
}
