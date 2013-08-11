<?php
namespace Metagist\ServerBundle\Controller;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides access to often used resources.
 * 
 * @author Daniel Pozzi <bonndan76@googlemail.com>
 */
class ServiceProvider
{
    /**
     * the service container
     * 
     * @var \Symfony\Component\DependencyInjection\ContainerInterface 
     */
    private $container;
    
    /**
     * Constructor.
     * 
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }
    
    /**
     * Provides access to the session.
     * 
     * @return \Symfony\Component\HttpFoundation\Session\Session;
     */
    public function session()
    {
        return $this['session'];
    }
    
    /**
     * Provides access to the logger.
     * 
     * @return \Monolog\Logger
     */
    public function logger()
    {
        return $this['monolog'];
    }
    
    /**
     * Returns the package repository.
     * 
     * @return \Metagist\PackageRepository
     */
    public function packages()
    {
        return $this[ServiceProvider::PACKAGE_REPO];
    }
    
    /**
     * Returns the metainfo repository (proxy).
     * 
     * @return \Metagist\MetaInfoRepository
     */
    public function metainfo()
    {
        $proxy = new MetaInfoRepositoryProxy(
            $this[ServiceProvider::METAINFO_REPO],
            $this->security(),
            $this->categories()
        );
        return $proxy;
    }
    
    /**
     * Returns the metainfo repository.
     * 
     * @return \Metagist\RatingRepository
     */
    public function ratings()
    {
        return $this[ServiceProvider::RATINGS_REPO];
    }
    
    /**
     * Returns the category schema representation.
     * 
     * @return \Metagist\CategorySchema
     */
    public function categories()
    {
        return $this[ServiceProvider::CATEGORY_SCHEMA];
    }

    /**
     * Returns the security context.
     * 
     * @return \Symfony\Component\Security\Core\SecurityContext
     */
    public function security()
    {
        return $this['security'];
    }
    
    /**
     * Returns the api provider.
     * 
     * @return \Metagist\Api\ApiProviderInterface
     */
    public function getApi()
    {
        return $this[\Metagist\Api\ServiceProvider::API];
    }
    
    /**
     * Returns the opauth listener (used to authenticate users).
     * 
     * @todo rework this. The listener should maybe not be provided here.
     * @return \Metagist\OpauthListener
     */
    public function getOpauthListener()
    {
        return $this[\Metagist\OpauthSecurityServiceProvider::LISTENER];
    }
}