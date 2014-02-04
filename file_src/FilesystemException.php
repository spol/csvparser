<?php

namespace Spol\Filesystem;

class FilesystemException extends \Exception
{
    public function __construct($path, $message = null, $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
