<?php

if (class_exists('PHP_CodeSniffer_Standards_AbstractScopeSniff', true) === false) {
    throw new PHP_CodeSniffer_Exception('Class PHP_CodeSniffer_Standards_AbstractScopeSniff not found');
}

class SW_Sniffs_Methods_MethodDeclarationSniff extends PHP_CodeSniffer_Standards_AbstractScopeSniff
{
    /**
     * Constructs a Squiz_Sniffs_Scope_MethodScopeSniff.
     */
    public function __construct()
    {
        parent::__construct(array(T_CLASS, T_INTERFACE), array(T_FUNCTION));

    }

    /**
     * Processes the function tokens within the class.
     *
     * @param PHP_CodeSniffer_File $phpcsFile The file where this token was found.
     * @param int                  $stackPtr  The position where the token was found.
     * @param int                  $currScope The current scope opener token.
     *
     * @return null
     */
    protected function processTokenWithinScope(PHP_CodeSniffer_File $phpcsFile, $stackPtr, $currScope)
    {
        $tokens = $phpcsFile->getTokens();

        $methodName = $phpcsFile->getDeclarationName($stackPtr);
        if ($methodName === null) {
            // Ignore closures.
            return;
        }

        /** Probably a better way to implement this */
        $type = ($tokens[$stackPtr-2]);
        if ($type['code'] == T_STATIC) {
            $type = ($tokens[$stackPtr-4]);
        }
        $hasUnderscore = $tokens[$stackPtr]['content'][1] === '_';

        $showError = true;
        if (substr($methodName, 0, 2) == '__') {
            // __construct, __set, __toString
            $showError = false;
        } elseif ($methodName[0] == '_') {
            if ($type['code'] == T_PROTECTED || $type['code'] == T_PRIVATE) {
                $showError = false;
            }
        } elseif ($methodName[0] != '_' && $type['code'] == T_PUBLIC) {
            $showError = false;
        }

        if ($showError) {
            $error = 'Invalid Method name "%s". If protected or private, this should be prefixed with an underscore';
            $data  = array($methodName);
            $phpcsFile->addWarning($error, $stackPtr, 'Underscore', $data);
        }

        $visibility = 0;
        $static     = 0;
        $abstract   = 0;
        $final      = 0;

        $find   = PHP_CodeSniffer_Tokens::$methodPrefixes;
        $find[] = T_WHITESPACE;
        $prev   = $phpcsFile->findPrevious($find, ($stackPtr - 1), null, true);

        $prefix = $stackPtr;
        while (($prefix = $phpcsFile->findPrevious(PHP_CodeSniffer_Tokens::$methodPrefixes, ($prefix - 1), $prev)) !== false) {
            switch ($tokens[$prefix]['code'])
            {
                case T_STATIC:
                    $static = $prefix;
                    break;
                case T_ABSTRACT:
                    $abstract = $prefix;
                    break;
                case T_FINAL:
                    $final = $prefix;
                    break;
                default:
                    $visibility = $prefix;
                    break;
            }
        }

        if ($static !== 0 && $static < $visibility) {
            $error = 'The static declaration must come after the visibility declaration';
            $phpcsFile->addError($error, $static, 'StaticBeforeVisibility');
        }

        if ($visibility !== 0 && $final > $visibility) {
            $error = 'The final declaration must precede the visibility declaration';
            $phpcsFile->addError($error, $final, 'FinalAfterVisibility');
        }

        if ($visibility !== 0 && $abstract > $visibility) {
            $error = 'The abstract declaration must precede the visibility declaration';
            $phpcsFile->addError($error, $abstract, 'AbstractAfterVisibility');
        }
    }
}
