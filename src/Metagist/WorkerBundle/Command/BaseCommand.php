<?php
namespace Metagist\WorkerBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

abstract class BaseCommand extends ContainerAwareCommand
{
    /**
     * Returns the service provider.
     * 
     * @return \Metagist\ServerBundle\Controller\ServiceProvider
     */
    protected function getServiceProvider()
    {
        return $this->getContainer()->get('metagist.controller.serviceprovider');
    }
    
    /**
     * Enable console output.
     */
    protected function enableConsoleLogOutput()
    {
        $this->getServiceProvider()->logger()->pushHandler(
            new \Monolog\Handler\StreamHandler('php://stdout', \Monolog\Logger::INFO)
        );
    }
}