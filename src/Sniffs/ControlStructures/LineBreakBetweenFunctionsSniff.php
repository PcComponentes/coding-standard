<?php
declare(strict_types=1);

namespace PcComponentesCodingStandard\Sniffs\ControlStructures;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Fixer;
use PHP_CodeSniffer\Sniffs\Sniff;
use SlevomatCodingStandard\Helpers\TokenHelper;
use function in_array;
use function sprintf;
use const T_CATCH;
use const T_CLOSE_CURLY_BRACKET;
use const T_COMMA;
use const T_WHILE;

final class LineBreakBetweenFunctionsSniff implements Sniff
{
    public const CODE_LINE_BREAK_BETWEEN_FUNCTION = 'LineBreakBetweenFunctions';

    private const CODE_EXCEPTIONS = [
        T_COMMA,
        T_CATCH,
        T_ELSE,
        T_WHILE,
    ];

    /**
     * @return array
     */
    public function register()
    {
        return [
            T_CLOSE_CURLY_BRACKET,
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
        $current = $tokens[$stackPtr];
        $line = $current['line'];

        $nextPointer = TokenHelper::findNextEffective($phpcsFile, $stackPtr + 1);
        if (null === $nextPointer) {
            return;
        }

        $previousLinePointer = TokenHelper::findPrevious($phpcsFile, T_DOC_COMMENT_OPEN_TAG, $nextPointer);

        $next = $tokens[$nextPointer];
        $previous = null;
        if (null !== $previousLinePointer) {
            $previous = $tokens[$previousLinePointer];
        }

        if (true === in_array($next['code'], self::CODE_EXCEPTIONS)) {
            return;
        }

        if (false === in_array($next['code'], [T_CLOSE_CURLY_BRACKET, T_SEMICOLON]) && $next['line'] === ($line + 2)) {
            return;
        }

        if (T_CLOSE_CURLY_BRACKET === $next['code'] && $next['line'] === ($line + 1)) {
            return;
        }

        if (null !== $previous && T_DOC_COMMENT_OPEN_TAG === $previous['code'] && $previous['line'] === ($line + 2)) {
            return;
        }

        if (T_SEMICOLON === ($next['code'] ?? null) && T_CLOSE_CURLY_BRACKET === ($tokens[$nextPointer + 2]['code'] ?? null)) {
            return;
        }

        if (T_SEMICOLON === $next['code'] && T_CLOSE_CURLY_BRACKET === $current['code']) {
            return;
        }

        $fix = $phpcsFile->addFixableError(
            sprintf('There must be exactly %d line break after a function.', 1),
            $stackPtr,
            self::CODE_LINE_BREAK_BETWEEN_FUNCTION
        );

        if (!$fix) {
            return;
        }

        $lineBreaks = $next['line'] - $line;

        if ($next['code'] === T_CLOSE_CURLY_BRACKET) {
            $this->setNumberOfLineBreaks($phpcsFile->fixer, $stackPtr, $lineBreaks, 1);
        } else {
            $this->setNumberOfLineBreaks($phpcsFile->fixer, $stackPtr, $lineBreaks, 2);
        }
    }

    private function setNumberOfLineBreaks(Fixer $fixer, int $stackPtr, int $currentLines, int $expectedLines): void
    {
        $lineBreaksToDelete = $currentLines - $expectedLines;

        $fixer->beginChangeset();
        for ($i = 1; $i <= $lineBreaksToDelete; $i++) {
            $fixer->replaceToken($stackPtr + $i, '');
        }
        for ($i = 0; $i > $lineBreaksToDelete; $i--) {
            $fixer->addNewline($stackPtr);
        }
        $fixer->endChangeset();
    }
}
