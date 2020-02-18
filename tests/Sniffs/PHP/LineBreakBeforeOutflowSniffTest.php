<?php
declare(strict_types=1);

namespace PcComponentesCodingStandard\Sniffs\PHP;

use SlevomatCodingStandard\Sniffs\TestCase;

class LineBreakBeforeOutflowSniffTest extends TestCase
{
    public function testNoErrors(): void
    {
        $report = self::checkFile(__DIR__ . '/data/lineBreakBeforeOutflowErrors.fixed.php');
        self::assertNoSniffErrorInFile($report);
    }

    public function testErrors(): void
    {
        $report = self::checkFile(__DIR__ . '/data/lineBreakBeforeOutflowErrors.php');
        self::assertSame(12, $report->getErrorCount());
        self::assertSniffError($report, 7, LineBreakBeforeOutflowSniff::CODE_LINE_BREAK_BEFORE_OUTFLOW);
        self::assertSniffError($report, 12, LineBreakBeforeOutflowSniff::CODE_LINE_BREAK_BEFORE_OUTFLOW);
        self::assertSniffError($report, 17, LineBreakBeforeOutflowSniff::CODE_LINE_BREAK_BEFORE_OUTFLOW);
        self::assertSniffError($report, 22, LineBreakBeforeOutflowSniff::CODE_LINE_BREAK_BEFORE_OUTFLOW);
        self::assertSniffError($report, 27, LineBreakBeforeOutflowSniff::CODE_LINE_BREAK_BEFORE_OUTFLOW);
        self::assertSniffError($report, 32, LineBreakBeforeOutflowSniff::CODE_LINE_BREAK_BEFORE_OUTFLOW);
        self::assertSniffError($report, 37, LineBreakBeforeOutflowSniff::CODE_LINE_BREAK_BEFORE_OUTFLOW);
        self::assertSniffError($report, 42, LineBreakBeforeOutflowSniff::CODE_LINE_BREAK_BEFORE_OUTFLOW);
        self::assertSniffError($report, 47, LineBreakBeforeOutflowSniff::CODE_LINE_BREAK_BEFORE_OUTFLOW);
        self::assertSniffError($report, 52, LineBreakBeforeOutflowSniff::CODE_LINE_BREAK_BEFORE_OUTFLOW);
        self::assertSniffError($report, 58, LineBreakBeforeOutflowSniff::CODE_LINE_BREAK_BEFORE_OUTFLOW);
        self::assertSniffError($report, 61, LineBreakBeforeOutflowSniff::CODE_LINE_BREAK_BEFORE_OUTFLOW);
        self::assertAllFixedInFile($report);
    }
}
