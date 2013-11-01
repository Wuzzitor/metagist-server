<?php
namespace Metagist\ServerBundle;

/**
 * Exception class.
 * 
 * @author Daniel Pozzi <bonndan76@googlemail.com>
 */
class Exception extends \Exception
{
    /**
     * exception code if package has not been found
     * @var int
     */
    const PACKAGE_NOT_FOUND = 404;
    
    /**
     * common code for internal malfunctions
     * 
     * @var int
     */
    const APPLICATION_EXCEPTION = 500;
}
