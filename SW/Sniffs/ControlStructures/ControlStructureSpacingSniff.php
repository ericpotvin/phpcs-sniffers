<?php

class SW_Sniffs_ControlStructures_ControlStructureSpacingSniff implements PHP_CodeSniffer_Sniff
{
    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @return array
     */
    public function register()
    {
        return array(
                T_IF,
                T_WHILE,
                T_FOREACH,
                T_FOR,
                T_SWITCH,
                T_DO,
                T_ELSE,
                T_ELSEIF,
               );

    }

    /**
     * Processes this test, when one of its tokens is encountered.
     *
     * @param PHP_CodeSniffer_File $phpcsFile The file being scanned.
     * @param int                  $stackPtr  The position of the current token
     *                                        in the stack passed in $tokens.
     *
     * @return null
     */
    public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();

        if (isset($tokens[$stackPtr]['parenthesis_opener']) === true) {
            $parenOpener = $tokens[$stackPtr]['parenthesis_opener'];
            $parenCloser = $tokens[$stackPtr]['parenthesis_closer'];
            if ($tokens[($parenOpener + 1)]['code'] === T_WHITESPACE) {
                $gap   = strlen($tokens[($parenOpener + 1)]['content']);
                $error = 'Expected 0 spaces after opening bracket; %s found';
                $data  = array($gap);
                $phpcsFile->addError($error, ($parenOpener + 1), 'SpacingAfterOpenBrace', $data);
            }

            if ($tokens[$parenOpener]['line'] === $tokens[$parenCloser]['line']
                && $tokens[($parenCloser - 1)]['code'] === T_WHITESPACE
            ) {
                $gap   = strlen($tokens[($parenCloser - 1)]['content']);
                $error = 'Expected 0 spaces before closing bracket; %s found';
                $data  = array($gap);
                $phpcsFile->addError($error, ($parenCloser - 1), 'SpaceBeforeCloseBrace', $data);
            }
        }
    }
}
