<?php

namespace PccomponentesCodingStandard\Sniffs\Namespaces;

use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;

final class ContextCouplingSniff implements Sniff
{
    /** @var string */
    public $projectNamespacePrefix = '';

    /** @var array */
    public $relationshipOfContexts = [];

    /**
     * @return array
     */
    public function register()
    {
        return [
            T_NAMESPACE,
        ];
    }

    /**
     * @param File $file
     * @param int $position
     * @return void
     */
    public function process(File $file, $position)
    {
        $tokens = $file->getTokens();

        $startNamespace = $file->findNext(
            [T_STRING],
            $file->findStartOfStatement($position)
        );
        $endPosition = $file->findEndOfStatement($startNamespace);

        $namespace = '';
        for ($i = $startNamespace; $i < $endPosition; $i++) {
            $namespace .= $tokens[$i]['content'];
        }

        $allowedNamespaces = [];
        foreach ($this->relationshipOfContexts as $context => $namespaces) {
            if (false !== strpos($namespace, $context)) {

                $allowedNamespaces = explode(',', $namespaces);
                break;
            }
        }

        if (0 === count($allowedNamespaces)) {
            return;
        }

        $uses = [];
        while (false !== $file->findNext([T_USE], $endPosition)) {
            $startPosition = $file->findNext(
                [T_STRING],
                $file->findNext([T_USE], $endPosition)
            );

            $endPosition = $file->findEndOfStatement($startPosition);

            $value = '';
            for ($i = $startPosition; $i < $endPosition; $i++) {
                $value .= $tokens[$i]['content'];
            }

            $uses[] = [
                'value' => $value,
                'stack_ptr' => $startPosition,
            ];
        }

        if (0 === count($uses)) {
            return;
        }

        foreach ($uses as $use) {
            if (false === strpos($use['value'], $this->projectNamespacePrefix)) {
                continue;
            }

            $found = false;
            foreach ($allowedNamespaces as $namespace) {
                if (false !== strpos($use['value'], $namespace)) {
                    $found = true;
                }
            }

            if (false === $found) {
                $error = 'The coupling between contexts is not allowed';
                $file->addError($error, $use['stack_ptr'], 'BlankLineAfter');
            }
        }
    }
}
