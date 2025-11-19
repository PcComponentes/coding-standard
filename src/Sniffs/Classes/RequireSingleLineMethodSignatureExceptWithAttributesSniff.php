<?php
declare(strict_types=1);

namespace PcComponentesCodingStandard\Sniffs\Classes;

use Exception;
use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use function count;
use function preg_match;
use function preg_replace;
use function sprintf;
use function strlen;
use function assert;
use function is_string;
use function str_replace;
use function rtrim;
use const T_FUNCTION;
use const T_OPEN_CURLY_BRACKET;
use const T_SEMICOLON;
use const T_ATTRIBUTE;
use const T_WHITESPACE;
use const T_ANON_CLASS;
use const T_CLASS;
use const T_INTERFACE;
use const T_TRAIT;
use const T_ENUM;

/**
 * Custom sniff that requires single-line method signatures,
 * except when attributes are present in parameters.
 *
 * Based on SlevomatCodingStandard\Sniffs\Classes\RequireSingleLineMethodSignatureSniff
 */
class RequireSingleLineMethodSignatureExceptWithAttributesSniff implements Sniff
{
    public const CODE_REQUIRED_SINGLE_LINE_SIGNATURE = 'RequiredSingleLineSignature';

    public int $maxLineLength = 120;

    /** @var list<string> */
    public array $includedMethodPatterns = [];

    /** @var list<string>|null */
    public ?array $includedMethodNormalizedPatterns = null;

    /** @var list<string> */
    public array $excludedMethodPatterns = [];

    /** @var list<string>|null */
    public ?array $excludedMethodNormalizedPatterns = null;

    public function register(): array
    {
        return [T_FUNCTION];
    }

    public function process(File $phpcsFile, $methodPointer): void
    {
        $this->maxLineLength = $this->normalizeInteger($this->maxLineLength);

        if (!$this->isMethod($phpcsFile, $methodPointer)) {
            return;
        }

        $tokens = $phpcsFile->getTokens();

        [$signatureStartPointer, $signatureEndPointer] = $this->getSignatureStartAndEndPointers($phpcsFile, $methodPointer);

        if ($tokens[$signatureStartPointer]['line'] === $tokens[$signatureEndPointer]['line']) {
            return;
        }

        // CUSTOM: Check if there are attributes in the method parameters
        if ($this->hasAttributesInParameters($phpcsFile, $methodPointer)) {
            // Allow multi-line when attributes are present
            return;
        }

        $signature = $this->getSignature($phpcsFile, $signatureStartPointer, $signatureEndPointer);
        $methodName = $this->getName($phpcsFile, $methodPointer);

        if (
            count($this->includedMethodPatterns) !== 0
            && !$this->isMethodNameInPatterns($methodName, $this->getIncludedMethodNormalizedPatterns())
        ) {
            return;
        }

        if (
            count($this->excludedMethodPatterns) !== 0
            && $this->isMethodNameInPatterns($methodName, $this->getExcludedMethodNormalizedPatterns())
        ) {
            return;
        }

        if ($this->maxLineLength !== 0 && strlen($signature) > $this->maxLineLength) {
            return;
        }

        $error = sprintf('Signature of method "%s" should be placed on a single line.', $methodName);
        $fix = $phpcsFile->addFixableError($error, $methodPointer, self::CODE_REQUIRED_SINGLE_LINE_SIGNATURE);
        if (!$fix) {
            return;
        }

        $phpcsFile->fixer->beginChangeset();

        $this->fixerChange($phpcsFile, $signatureStartPointer, $signatureEndPointer, $signature);

        $phpcsFile->fixer->endChangeset();
    }

    /**
     * CUSTOM: Check if method has attributes in parameters
     */
    private function hasAttributesInParameters(File $phpcsFile, int $methodPointer): bool
    {
        $tokens = $phpcsFile->getTokens();

        if (!isset($tokens[$methodPointer]['parenthesis_opener'])
            || !isset($tokens[$methodPointer]['parenthesis_closer'])
        ) {
            return false;
        }

        $openParenthesis = $tokens[$methodPointer]['parenthesis_opener'];
        $closeParenthesis = $tokens[$methodPointer]['parenthesis_closer'];

        for ($i = $openParenthesis + 1; $i < $closeParenthesis; $i++) {
            if ($tokens[$i]['code'] === T_ATTRIBUTE) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array<int, int>
     */
    protected function getSignatureStartAndEndPointers(File $phpcsFile, int $methodPointer): array
    {
        $signatureStartPointer = $this->findFirstTokenOnLine($phpcsFile, $methodPointer);

        /** @var int $pointerAfterSignatureEnd */
        $pointerAfterSignatureEnd = $this->findNext($phpcsFile, [T_OPEN_CURLY_BRACKET, T_SEMICOLON], $methodPointer + 1);
        if ($phpcsFile->getTokens()[$pointerAfterSignatureEnd]['code'] === T_SEMICOLON) {
            return [$signatureStartPointer, $pointerAfterSignatureEnd];
        }

        /** @var int $signatureEndPointer */
        $signatureEndPointer = $this->findPreviousEffective($phpcsFile, $pointerAfterSignatureEnd - 1);

        return [$signatureStartPointer, $signatureEndPointer];
    }

    protected function getSignature(File $phpcsFile, int $signatureStartPointer, int $signatureEndPointer): string
    {
        $signature = $this->getContent($phpcsFile, $signatureStartPointer, $signatureEndPointer);
        $signature = preg_replace(sprintf('~%s[ \t]*~', $phpcsFile->eolChar), ' ', $signature);
        assert(is_string($signature));

        $signature = str_replace(['( ', ' )'], ['(', ')'], $signature);
        $signature = rtrim($signature);

        return $signature;
    }

    /**
     * @param list<string> $normalizedPatterns
     */
    private function isMethodNameInPatterns(string $methodName, array $normalizedPatterns): bool
    {
        foreach ($normalizedPatterns as $pattern) {
            if (!$this->isValidRegularExpression($pattern)) {
                throw new Exception(sprintf('%s is not valid PCRE pattern.', $pattern));
            }

            if (preg_match($pattern, $methodName) !== 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return list<string>
     */
    private function getIncludedMethodNormalizedPatterns(): array
    {
        $this->includedMethodNormalizedPatterns ??= $this->normalizeArray($this->includedMethodPatterns);
        return $this->includedMethodNormalizedPatterns;
    }

    /**
     * @return list<string>
     */
    private function getExcludedMethodNormalizedPatterns(): array
    {
        $this->excludedMethodNormalizedPatterns ??= $this->normalizeArray($this->excludedMethodPatterns);
        return $this->excludedMethodNormalizedPatterns;
    }

    // Helper methods simplified from Slevomat helpers

    private function normalizeInteger(int $value): int
    {
        return $value;
    }

    private function normalizeArray(array $value): array
    {
        return $value;
    }

    private function isValidRegularExpression(string $pattern): bool
    {
        return @preg_match($pattern, '') !== false;
    }

    private function isMethod(File $phpcsFile, int $functionPointer): bool
    {
        $tokens = $phpcsFile->getTokens();

        if (!isset($tokens[$functionPointer]['conditions'])) {
            return false;
        }

        foreach ($tokens[$functionPointer]['conditions'] as $conditionTokenCode) {
            if ($conditionTokenCode === T_ANON_CLASS || $conditionTokenCode === T_CLASS || $conditionTokenCode === T_INTERFACE || $conditionTokenCode === T_TRAIT || $conditionTokenCode === T_ENUM) {
                return true;
            }
        }

        return false;
    }

    private function getName(File $phpcsFile, int $functionPointer): string
    {
        return $phpcsFile->getDeclarationName($functionPointer);
    }

    private function findFirstTokenOnLine(File $phpcsFile, int $pointer): int
    {
        $tokens = $phpcsFile->getTokens();
        $line = $tokens[$pointer]['line'];

        do {
            $pointer--;
        } while (isset($tokens[$pointer]) && $tokens[$pointer]['line'] === $line);

        return $pointer + 1;
    }

    private function findNext(File $phpcsFile, array $types, int $start): ?int
    {
        return $phpcsFile->findNext($types, $start);
    }

    private function findPreviousEffective(File $phpcsFile, int $pointer): ?int
    {
        return $phpcsFile->findPrevious(T_WHITESPACE, $pointer, null, true);
    }

    private function getContent(File $phpcsFile, int $start, int $end): string
    {
        $tokens = $phpcsFile->getTokens();
        $content = '';

        for ($i = $start; $i <= $end; $i++) {
            $content .= $tokens[$i]['content'];
        }

        return $content;
    }

    private function fixerChange(File $phpcsFile, int $start, int $end, string $replacement): void
    {
        for ($i = $start; $i <= $end; $i++) {
            $phpcsFile->fixer->replaceToken($i, '');
        }

        $phpcsFile->fixer->addContent($start, $replacement);
    }
}
