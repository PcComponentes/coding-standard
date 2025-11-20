<?php
declare(strict_types=1);

namespace PcComponentesCodingStandard\Sniffs\Classes;

use Exception;
use PHP_CodeSniffer\Files\File;
use SlevomatCodingStandard\Helpers\FixerHelper;
use SlevomatCodingStandard\Helpers\FunctionHelper;
use SlevomatCodingStandard\Helpers\SniffSettingsHelper;
use SlevomatCodingStandard\Sniffs\Classes\RequireSingleLineMethodSignatureSniff;
use function count;
use function preg_match;
use function sprintf;
use function strlen;
use const T_ATTRIBUTE;

/**
 * Custom sniff that requires single-line method signatures,
 * except when attributes are present in parameters.
 *
 * Based on SlevomatCodingStandard\Sniffs\Classes\RequireSingleLineMethodSignatureSniff (v8.22.1)
 */
class RequireSingleLineMethodSignatureExceptWithAttributesSniff extends RequireSingleLineMethodSignatureSniff
{
    public function process(File $phpcsFile, $methodPointer): void
    {
        $this->maxLineLength = SniffSettingsHelper::normalizeInteger($this->maxLineLength);

        if (!FunctionHelper::isMethod($phpcsFile, $methodPointer)) {
            return;
        }

        $tokens = $phpcsFile->getTokens();

        [$signatureStartPointer, $signatureEndPointer] = $this->getSignatureStartAndEndPointers($phpcsFile, $methodPointer);

        if ($tokens[$signatureStartPointer]['line'] === $tokens[$signatureEndPointer]['line']) {
            return;
        }

        #region CUSTOM: Check if there are attributes in the method parameters
        if ($this->hasAttributesInParameters($phpcsFile, $methodPointer)) {
            // Allow multi-line when attributes are present
            return;
        }
        #endregion

        $signature = $this->getSignature($phpcsFile, $signatureStartPointer, $signatureEndPointer);
        $methodName = FunctionHelper::getName($phpcsFile, $methodPointer);

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

        FixerHelper::change($phpcsFile, $signatureStartPointer, $signatureEndPointer, $signature);

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
     * @param list<string> $normalizedPatterns
     */
    private function isMethodNameInPatterns(string $methodName, array $normalizedPatterns): bool
    {
        foreach ($normalizedPatterns as $pattern) {
            if (!SniffSettingsHelper::isValidRegularExpression($pattern)) {
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
        $this->includedMethodNormalizedPatterns ??= SniffSettingsHelper::normalizeArray($this->includedMethodPatterns);
        return $this->includedMethodNormalizedPatterns;
    }

    /**
     * @return list<string>
     */
    private function getExcludedMethodNormalizedPatterns(): array
    {
        $this->excludedMethodNormalizedPatterns ??= SniffSettingsHelper::normalizeArray($this->excludedMethodPatterns);
        return $this->excludedMethodNormalizedPatterns;
    }
}
