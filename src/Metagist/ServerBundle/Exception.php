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
     * exception code is package has not been found
     * @var int
     */
    const PACKAGE_NOT_FOUND = 404;
}
