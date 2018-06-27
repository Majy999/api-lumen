<?php
namespace App\Exceptions;

class RequestException extends \Exception
{
    function __construct($msg='')
    {
        parent::__construct($msg);
    }
}