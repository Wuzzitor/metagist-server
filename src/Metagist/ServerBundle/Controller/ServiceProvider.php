<?php
namespace Metagist\ServerBundle\Controller;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

/**
 * Provides access to often used resources.
 * 
 * @author Daniel Pozzi <bonndan76@googlemail.com>
 */
class ServiceProvider
{
    /**
     * container
     * 
     * @var ContainerInterface
     */
    private $application;

    public function __construct(ContainerInterface $container = null)
    {
        $this->application = $container;
    }
    
     /**
     * Retrieves a package either from the db or packagist.
     * 
     * @param string $author
     * @param string $name
     * @return Package
     */
    public function getPackage($author, $name)
    {
        $packageRepo = $this->application->packages();
        $package = $packageRepo->byAuthorAndName($author, $name);
        if ($package == null) {
            $factory = $this->application[ServiceProvider::PACKAGE_FACTORY];
            /* @var $factory PackageFactory */
            $package = $factory->byAuthorAndName($author, $name);
            if ($packageRepo->save($package)) {
                /* @var $metaInfoRepo MetaInfoRepository */
                $metaInfoRepo = $this->application[ServiceProvider::METAINFO_REPO];
                $metaInfoRepo->savePackage($package);
            }
        }

        return $package;
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
        return $this->getRepo('MetagistServerBundle:Package');
    }
    
    /**
     * Returns the metainfo repository (proxy).
     * 
     * @return \Metagist\ServerBundle\Entity\MetainfoRepository
     */
    public function metainfo()
    {
        $metainfoRepo = $this->getRepo('MetagistServerBundle:Metainfo');
        $metainfoRepo->setValidator($this->getValidator());
        
        $proxy = new \Metagist\ServerBundle\Entity\MetainfoRepositoryProxy(
            $metainfoRepo,
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
        return $this->getRepo('MetagistServerBundle:Rating');
    }
    
    /**
     * Returns the category schema representation.
     * 
     * @return \Metagist\ServerBundle\Resources\CategorySchema
     */
    public function categories()
    {
        return $this->application->get('metagist.categoryschema');
    }

    /**
     * Returns the security context.
     * 
     * @return \Symfony\Component\Security\Core\SecurityContext
     */
    public function security()
    {
        return $this->application->get('security.context');
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
    
    /**
     * Returns the repo for an entity.
     * 
     * @param string $entityName
     * @return \Doctrine\ORM\EntityRepository
     */
    private function getRepo($entityName)
    {
        return $this->application->get('doctrine')->getManager()
            ->getRepository($entityName);
    }
    
    /**
     * Returns the validator.
     * 
     * @return \Metagist\ServerBundle\Resources\Validator
     */
    private function getValidator()
    {
        return $this->application->get('metagist.validator');
    }
}