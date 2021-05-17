<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Support\Arr;

class CustomException extends Exception
{
    protected $options;
    protected $error_code;

    public function __construct($options = array(), $error_code)
    {
        $this->options = $options;
        $this->error_code = $error_code;
    }

    /**
     * Report the exception.
     *
     * @return void
     */
    public function report()
    {
    }
 
    /**
     * Render the exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request
     * @return \Illuminate\Http\Response
     */
    public function render($request)
    {
        return response()->json($this->options, $this->error_code);
    }
}
