<?php
declare(strict_types=1);

namespace PcComponentesCodingStandard\Sniffs\Namespaces;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use SlevomatCodingStandard\Helpers\TokenHelper;
use function function_exists;
use function sprintf;
use const T_NS_SEPARATOR;
use const T_STRING;

final class RequireNamespaceInNativeFunctionSniff implements Sniff
{
    public const CODE_REQUIRE_NAMESPACE_IN_NATIVE_FUNCTION = 'RequireNamespaceInNativeFunction';

    /**
     * @return array
     */
    public function register()
    {
        return [
            T_STRING,
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
        $content = $tokens[$stackPtr]['content'];
        if (false === function_exists($content)) {
            return;
        }

        if (false !== $phpcsFile->findFirstOnLine([T_USE, T_NAMESPACE, T_FUNCTION, T_DOUBLE_COLON, T_OBJECT_OPERATOR], $stackPtr)) {
            return;
        }

        if ($tokens[$stackPtr + 1]['code'] !== T_OPEN_PARENTHESIS) {
            return;
        }

        $beforePointer = TokenHelper::findPreviousEffective($phpcsFile, $stackPtr - 1);
        $beforeCode = $tokens[$beforePointer]['code'];

        if ($beforeCode === T_NS_SEPARATOR) {
            return;
        }

        $fix = $phpcsFile->addFixableError(
            sprintf(
                'Namespace is mandatory in native php function: %s()',
                $content
            ),
            $stackPtr,
            self::CODE_REQUIRE_NAMESPACE_IN_NATIVE_FUNCTION
        );

        if (!$fix) {
            return;
        }

        $phpcsFile->fixer->beginChangeset();
        $phpcsFile->fixer->replaceToken($stackPtr, '\\' . $content);
        $phpcsFile->fixer->endChangeset();
    }
}
