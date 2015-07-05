<?php

namespace Prime;

class ErrorHandler
{
    /**
     * Throw an Exception, thus halting the application, for each error 
     * encountered (this includes also notices if the error_reporting is 
     * configured to display them)
     * 
     * @var boolean
     */
    protected $throwExceptionsInsteadOfErrors = false;

    /**
     * Whether to display errors or not
     * 
     * @var boolean
     */
    protected $displayErrors = true;


    public function __construct($register = false)
    {
        if (ini_get('display_errors') == false) {
            $this->displayErrors(false);
        }

        // Errors that should really be fatal and halt the execition of the program
        $this->fatalErrors = E_ERROR | E_USER_ERROR | E_COMPILE_ERROR | E_CORE_ERROR | E_PARSE;

        if ($register) {
            $this->register();
        }
    }

    public function register()
    {
        ini_set('display_errors', false);

        set_error_handler(array($this, 'handleError'));
        set_exception_handler(array($this, 'handleException'));
        register_shutdown_function(array($this, 'handleShutdown'));
    }

    public function deregister()
    {
        restore_error_handler();
        restore_exception_handler();
    }

    public function handleError($severity, $message, $file, $line) 
    {
        if (!(error_reporting() & $severity)) {
            // This error code is not included in error_reporting
            // But if the error is really fatal, exit with proper code
            if ($severity & $this->fatalErrors) {
                $this->end(1);
            }

            return true;
        }

        $e = new \ErrorException($message, 0, $severity, $file, $line);
        if ($this->throwExceptionsForErrors) {
            throw $e;
        } else {
            $this->handleException($e);                
        }

        if ($severity & $this->fatalErrors) {
            $this->end(1);
        }
    }

    public function handleException(\Exception $e)
    {
        if ($this->displayErrors) {
            // simple displaying of the error message and trace
            $error = $e->__toString();
            $trace = $e->getTraceAsString();

            $cli = substr(php_sapi_name(), 0, 3) == 'cli';
            echo $cli ? $error : nl2br($error);
            echo $cli ? $trace : nl2br($trace);
        }
    }

    /**
     * When we are dealing with a fatal error
     */
    public function handleShutdown()
    {
        // Cannot throw exceptions in the shutdown handler
        $this->throwExceptionsInsteadOfErrors(false);

        $error = error_get_last();
        if ($error && $error['type'] & $this->fatalErrors) {
            $this->handleError(
                $error['type'], $error['message'], $error['file'], $error['line']
            );
        }
    }

    public function throwExceptionsInsteadOfErrors($bool)
    {
        $this->throwExceptionsInsteadOfErrors = (bool) $bool;
    }

    /**
     * Terminate the program with the specified code. A code greater than 0 it
     * means that something went wrong.
     * 
     * @param  integer $code 
     */
    protected function end($code = 0)
    {
        exit($code);
    }
}
