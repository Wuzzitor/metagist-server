<?php
namespace Metagist\ServerBundle\Controller;

use Doctrine\Common\Cache\ApcCache;
use Doctrine\ORM\EntityRepository;
use Guzzle\Cache\DoctrineCacheAdapter;
use Guzzle\Http\Client as Client2;
use Guzzle\Http\Message\Header\CacheControl;
use Guzzle\Http\Message\Response;
use Guzzle\Plugin\Cache\CachePlugin;
use Guzzle\Plugin\Cache\CallbackCanCacheStrategy;
use Guzzle\Plugin\Cache\DefaultCacheStorage;
use Metagist\Api\Exception;
use Metagist\ServerBundle\Entity\DependencyRepository;
use Metagist\ServerBundle\Entity\MetainfoFactory;
use Metagist\ServerBundle\Entity\MetainfoRepository;
use Metagist\ServerBundle\Entity\MetainfoRepositoryProxy;
use Metagist\ServerBundle\Entity\PackageFactory;
use Metagist\ServerBundle\Entity\PackageRepository;
use Metagist\ServerBundle\Entity\RatingRepository;
use Packagist\Api\Client as PackagistClient;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\Templating\Asset\Package;

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
     * @param ContainerInterface $container
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
     * @throws Exception
     */
    public function getPackage($author, $name)
    {
        $package = $this->packages()->byAuthorAndName($author, $name);
        if ($package !== null) {
            return $package;
        }
        
        $factory = $this->getPackageFactory();
        $package = $factory->byAuthorAndName($author, $name);
        return $this->packages()->save($package);
    }
    
    /**
     * Returns the package factory.
     * 
     * @return PackageFactory
     */
    private function getPackageFactory()
    {
        return new PackageFactory(
            $this->getPackagistApiClient(),
            new MetainfoFactory($this->logger())
        );
    }


    /**
     * Provides access to the session.
     * 
     * @return Session
     */
    public function session()
    {
        return $this->container->get('session');
    }
    
    /**
     * Provides access to the logger.
     * 
     * @return Logger
     */
    public function logger()
    {
        return $this->container->get('logger');
    }
    
    /**
     * Returns the package repository.
     * 
     * @return PackageRepository
     */
    public function packages()
    {
        $repo = $this->getRepo('MetagistServerBundle:Package');
        $repo->setValidator($this->getValidator());
        return $repo;
    }
    
    /**
     * Returns the metainfo repository proxy.
     * 
     * @return MetainfoRepositoryProxy
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
     * @return RatingRepository
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
     * Returns the dependency repo.
     * 
     * @return DependencyRepository
     */
    public function dependencies()
    {
        return $this->getRepo('MetagistServerBundle:Dependency');
    }
    
    /**
     * Returns the security context.
     * 
     * @return SecurityContext
     */
    public function security()
    {
        return $this->container->get('security.context');
    }
    
    /**
     * Returns the repo for an entity.
     * 
     * @param string $entityName
     * @return EntityRepository
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
    
    /**
     * Creates a packagist api client instance.
     * 
     * The api client uses a http client with an apc cache. Cache-control
     * headers of responses are ignored.
     * 
     * @return Client
     * @link http://guzzlephp.org/guide/http/caching.html
     */
    public function getPackagistApiClient()
    {
        $cache = new ApcCache();
        $adapter = new DoctrineCacheAdapter($cache);
        $cachePlugin = new CachePlugin(array(
            'storage' => new DefaultCacheStorage($adapter),
            'can_cache' => $this->getPackagistCanCacheStrategy()
        ));
        
        $httpClient = new \Guzzle\Http\Client();
        $httpClient->addSubscriber($cachePlugin);
        return new PackagistClient($httpClient);
    }
    
    /**
     * Creates a caching stragegy which caches all packagist api requests.
     * 
     * @return CallbackCanCacheStrategy
     */
    private function getPackagistCanCacheStrategy()
    {
        $callback = function(){return true;};
        $responseCallback = function(Response $response){
            /* @var $cacheControl \Guzzle\Http\Message\Header\CacheControl */
            $cacheControl = $response->getHeader('Cache-Control');
            if ($cacheControl) {
                $cacheControl->addDirective('max-age', 60);
            }
            return true;
        };
        $canCache = new CallbackCanCacheStrategy($callback, $responseCallback);
        
        return $canCache;
    }
}