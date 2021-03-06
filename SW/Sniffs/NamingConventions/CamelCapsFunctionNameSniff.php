<?php

if (class_exists('PHP_CodeSniffer_Standards_AbstractScopeSniff', true) === false) {
    throw new PHP_CodeSniffer_Exception('Class PHP_CodeSniffer_Standards_AbstractScopeSniff not found');
}

class SW_Sniffs_NamingConventions_CamelCapsFunctionNameSniff extends PHP_CodeSniffer_Standards_AbstractScopeSniff
{
    /**
     * A list of all PHP magic methods.
     *
     * @var array
     */
    protected $magicMethods = array(
                               'construct',
                               'destruct',
                               'call',
                               'callstatic',
                               'get',
                               'set',
                               'isset',
                               'unset',
                               'sleep',
                               'wakeup',
                               'tostring',
                               'set_state',
                               'clone',
                               'invoke',
                               'call',
                              );

    /**
     * A list of all PHP non-magic methods starting with a double underscore.
     *
     * These come from PHP modules such as SOAPClient.
     *
     * @var array
     */
    protected $methodsDoubleUnderscore = array(
                                          'soapcall',
                                          'getlastrequest',
                                          'getlastresponse',
                                          'getlastrequestheaders',
                                          'getlastresponseheaders',
                                          'getfunctions',
                                          'gettypes',
                                          'dorequest',
                                          'setcookie',
                                          'setlocation',
                                          'setsoapheaders',
                                         );

    /**
     * A list of all PHP magic functions.
     *
     * @var array
     */
    protected $magicFunctions = array('autoload');

    /**
     * If TRUE, the string must not have two capital letters next to each other.
     *
     * @var bool
     */
    public $strict = true;


    /**
     * Constructs a SW_Sniffs_NamingConventions_CamelCapsFunctionNameSniff.
     */
    public function __construct()
    {
        parent::__construct(array(T_CLASS, T_INTERFACE, T_TRAIT), array(T_FUNCTION), true);

    }//end __construct()


    /**
     * Processes the tokens within the scope.
     *
     * @param PHP_CodeSniffer_File $phpcsFile The file being processed.
     * @param int                  $stackPtr  The position where this token was
     *                                        found.
     * @param int                  $currScope The position of the current scope.
     *
     * @return void
     */
    protected function processTokenWithinScope(PHP_CodeSniffer_File $phpcsFile, $stackPtr, $currScope)
    {
        $methodName = $phpcsFile->getDeclarationName($stackPtr);
        if ($methodName === null) {
            // Ignore closures.
            return;
        }

        $className = $phpcsFile->getDeclarationName($currScope);
        $errorData = array($methodName . '()');

        // Is this a magic method. i.e., is prefixed with "__" ?
        if (preg_match('|^__|', $methodName) !== 0) {
            $magicPart = strtolower(substr($methodName, 2));
            if (in_array($magicPart, array_merge($this->magicMethods, $this->methodsDoubleUnderscore)) === false) {
                 $error = 'Method name "%s" is invalid; only PHP magic methods should be prefixed with a double underscore';
                 $phpcsFile->addError($error, $stackPtr, 'MethodDoubleUnderscore', $errorData);
            }

            return;
        }

        // PHP4 constructors are NOT allowed to break our rules.
        if ($methodName === $className) {
            $error = 'PHP4 constructor "%s" is invalid';
            $phpcsFile->addError($error, $stackPtr, 'MethodDoubleUnderscore', $errorData);
            return;
        }

        // PHP4 destructors are NOT allowed to break our rules.
        if ($methodName === '_'.$className) {
            $error = 'PHP4 destructor "%s" is invalid';
            $phpcsFile->addError($error, $stackPtr, 'MethodDoubleUnderscore', $errorData);
            return;
        }

        $methodProps = $phpcsFile->getMethodProperties($stackPtr);
        $public = true;

        if ($methodName[0] == '_') {
            $public = false;
        }

        if (PHP_CodeSniffer::isCamelCaps($methodName, false, $public, $this->strict) === false) {
            if ($methodProps['scope_specified'] === true) {
                $error = '%s method name "%s" is not in camel caps format !!';
                $data  = array(
                          ucfirst($methodProps['scope']),
                          $errorData[0],
                         );
                $phpcsFile->addError($error, $stackPtr, 'ScopeNotCamelCaps', $data);
            } else {
                $error = 'Method name "%s" is not in camel caps format !!!';
                $phpcsFile->addError($error, $stackPtr, 'NotCamelCaps', $errorData);
            }

            return;
        }

    }

    /**
     * Processes the tokens outside the scope.
     *
     * @param PHP_CodeSniffer_File $phpcsFile The file being processed.
     * @param int                  $stackPtr  The position where this token was
     *                                        found.
     *
     * @return void
     */
    protected function processTokenOutsideScope(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        $functionName = $phpcsFile->getDeclarationName($stackPtr);
        if ($functionName === null) {
            // Ignore closures.
            return;
        }

        $errorData = array($functionName);

        // Is this a magic function. i.e., it is prefixed with "__".
        if (preg_match('|^__|', $functionName) !== 0) {
            $magicPart = strtolower(substr($functionName, 2));
            if (in_array($magicPart, $this->magicFunctions) === false) {
                 $error = 'Function name "%s" is invalid; only PHP magic methods should be prefixed with a double underscore';
                 $phpcsFile->addError($error, $stackPtr, 'FunctionDoubleUnderscore', $errorData);
            }

            return;
        }

        if (PHP_CodeSniffer::isCamelCaps($functionName, false, true, $this->strict) === false) {
            $error = 'Function name "%s" is not in camel caps format';
            $phpcsFile->addError($error, $stackPtr, 'NotCamelCaps', $errorData);
        }
    }
}
