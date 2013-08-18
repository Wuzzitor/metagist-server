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
     * container
     * 
     * @var ContainerInterface
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
     * Retrieves a package either from the db or packagist.
     * 
     * @param string $author
     * @param string $name
     * @return Package
     */
    public function getPackage($author, $name)
    {
        $package = $this->packages()->byAuthorAndName($author, $name);
        if ($package == null) {
            $factory = $this->container[ServiceProvider::PACKAGE_FACTORY];
            /* @var $factory PackageFactory */
            $package = $factory->byAuthorAndName($author, $name);
            if ($this->packages()->save($package)) {
                /* @var $metaInfoRepo MetaInfoRepository */
                $metaInfoRepo = $this->metainfo();
                $metaInfoRepo->savePackage($package);
            }
        }

        return $package;
    }
    
    /**
     * Provides access to the session.
     * 
     * @return \Symfony\Component\HttpFoundation\Session\Session
     */
    public function session()
    {
        return $this->container->get('session');
    }
    
    /**
     * Provides access to the logger.
     * 
     * @return \Symfony\Bridge\Monolog\Logger
     */
    public function logger()
    {
        return $this->container->get('logger');
    }
    
    /**
     * Returns the package repository.
     * 
     * @return \Metagist\ServerBundle\Entity\PackageRepository
     */
    public function packages()
    {
        $repo = $this->getRepo('MetagistServerBundle:Package');
        $repo->setValidator($this->getValidator());
        return $repo;
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
     * @return \Metagist\ServerBundle\Entity\RatingRepository
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
        return $this->container->get('metagist.categoryschema');
    }

    /**
     * Returns the security context.
     * 
     * @return \Symfony\Component\Security\Core\SecurityContext
     */
    public function security()
    {
        return $this->container->get('security.context');
    }
    
    /**
     * Returns the api factory.
     * 
     * @return \Metagist\Api\FactoryInterface
     */
    public function getApiFactory()
    {
        return $this->container->get('metagist.api');
    }
    
    /**
     * Returns the repo for an entity.
     * 
     * @param string $entityName
     * @return \Doctrine\ORM\EntityRepository
     */
    private function getRepo($entityName)
    {
        return $this->container->get('doctrine')->getManager()
            ->getRepository($entityName);
    }
    
    /**
     * Returns the validator.
     * 
     * @return \Metagist\ServerBundle\Resources\Validator
     */
    private function getValidator()
    {
        return $this->container->get('metagist.validator');
    }
}