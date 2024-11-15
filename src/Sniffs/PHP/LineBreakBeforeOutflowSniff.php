<?php
declare(strict_types=1);

namespace PcComponentesCodingStandard\Sniffs\PHP;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use SlevomatCodingStandard\Helpers\TokenHelper;
use function array_key_exists;
use function sprintf;
use const T_BREAK;
use const T_COLON;
use const T_CONTINUE;
use const T_EXIT;
use const T_OPEN_CURLY_BRACKET;
use const T_RETURN;
use const T_THROW;

final class LineBreakBeforeOutflowSniff implements Sniff
{
    public const CODE_LINE_BREAK_BEFORE_OUTFLOW = 'LineBreakBeforeOutflow';

    private const DEFAULT_LINE_BREAKS = 2;
    private const LINE_BREAKS_EXCEPTIONS = [
        T_OPEN_CURLY_BRACKET => 1,
        T_COLON => 1,
        T_MATCH_ARROW => 0,
    ];

    /**
     * @return array
     */
    public function register()
    {
        return [
            T_CONTINUE,
            T_BREAK,
            T_RETURN,
            T_EXIT,
            T_THROW,
        ];
    }

    /**
     * @param File $phpcsFile
     * @param int $stackPtr
     * @return void
     */
    public function process(File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();

        $beforePointer = TokenHelper::findPreviousEffective($phpcsFile, $stackPtr - 1);
        $lineBreaks = $this->numberOfLineBreaks($tokens, $beforePointer, $stackPtr);

        $beforeCode = $tokens[$beforePointer]['code'];

        if (true === array_key_exists($beforeCode, self::LINE_BREAKS_EXCEPTIONS) && $lineBreaks === self::LINE_BREAKS_EXCEPTIONS[$beforeCode]) {
            return;
        }

        if (false === array_key_exists($beforeCode, self::LINE_BREAKS_EXCEPTIONS) && $lineBreaks === self::DEFAULT_LINE_BREAKS) {
            return;
        }

        $lineBreaksRequired = $this->getLineBreaksRequired($beforeCode);
        $fix = $phpcsFile->addFixableError(
            sprintf('There must be exactly %d line break before outflow.', $lineBreaksRequired),
            $stackPtr,
            self::CODE_LINE_BREAK_BEFORE_OUTFLOW
        );

        if (!$fix) {
            return;
        }

        $lineBreaksToDelete = $lineBreaks - $lineBreaksRequired;

        $phpcsFile->fixer->beginChangeset();

        for ($i = 1; $i <= $lineBreaksToDelete; $i++) {
            $phpcsFile->fixer->replaceToken($beforePointer + $i, '');
        }

        for ($i = 0; $i > $lineBreaksToDelete; $i--) {
            $phpcsFile->fixer->addNewline($beforePointer);
        }

        $phpcsFile->fixer->endChangeset();
    }

    private function getLineBreaksRequired(string $code): int
    {
        return true === array_key_exists($code, self::LINE_BREAKS_EXCEPTIONS)
            ? self::LINE_BREAKS_EXCEPTIONS[$code]
            : self::DEFAULT_LINE_BREAKS;
    }

    private function numberOfLineBreaks(array $tokens, int $start, int $finish): int
    {
        return $tokens[$finish - 1]['line'] - $tokens[$start + 1]['line'];
    }
}
