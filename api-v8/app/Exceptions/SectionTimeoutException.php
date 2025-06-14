<?php

namespace App\Exceptions;

use Exception;

class SectionTimeoutException extends Exception
{
    //
    public function __construct()
    {
        parent::__construct('section timeout');
    }
}
