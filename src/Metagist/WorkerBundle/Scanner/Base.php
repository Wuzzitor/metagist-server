<?php
namespace Metagist\WorkerBundle\Scanner;

/**
 * Base scanner class.
 * 
 * @author Daniel Pozzi <bonndan76@googlemail.com>
 */
abstract class Base
{
    /**
     * logger instance
     * 
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;
    
    /**
     * Constructor.
     * 
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(\Psr\Log\LoggerInterface $logger)
    {
        $this->logger      = $logger;
    }
}