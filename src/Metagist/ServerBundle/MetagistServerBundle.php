<?php
namespace Metagist\ServerBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Metagist\ServerBundle\DependencyInjection\Security\ApiFactory;

/**
 * Bundle class
 * 
 * 
 */
class MetagistServerBundle extends Bundle
{
    public function boot()
    {
        parent::boot();
        require_once $this->getPath(). '/version.php';
    }
    
    /**
     * Registers the api secuirty factory.
     * 
     * @param \Metagist\ServerBundle\ContainerBuilder $container
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $extension = $container->getExtension('security');
        $extension->addSecurityListenerFactory(new ApiFactory());
    }
}
