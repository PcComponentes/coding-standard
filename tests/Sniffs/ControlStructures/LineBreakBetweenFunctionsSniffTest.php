<?php
declare(strict_types=1);

namespace PcComponentesCodingStandard\Sniffs\ControlStructures;

use SlevomatCodingStandard\Sniffs\TestCase;

class LineBreakBetweenFunctionsSniffTest extends TestCase
{
    public function testNoErrors(): void
    {
        $report = self::checkFile(__DIR__ . '/data/lineBreakBetweenFunctionsErrors.fixed.php');
        self::assertNoSniffErrorInFile($report);
    }

    public function testErrors(): void
    {
        $report = self::checkFile(__DIR__ . '/data/lineBreakBetweenFunctionsErrors.php');
        self::assertSame(2, $report->getErrorCount());
        self::assertSniffError($report, 9, LineBreakBetweenFunctionsSniff::CODE_LINE_BREAK_BETWEEN_FUNCTION);
        self::assertSniffError($report, 11, LineBreakBetweenFunctionsSniff::CODE_LINE_BREAK_BETWEEN_FUNCTION);

        self::assertAllFixedInFile($report);
    }

    public function testLineBreakBeforeComment(): void
    {
        $report = self::checkFile(__DIR__ . '/data/lineBreakBeforeComment.php');
        self::assertNoSniffErrorInFile($report);
    }
}
