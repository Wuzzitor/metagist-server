<?php
namespace Metagist\ServerBundle\Entity;

use Metagist\ServerBundle\Entity\MetainfoFactory;
use Metagist\ServerBundle\Exception;
use Packagist\Api\Client as PackagistClient;

/**
 * Factory for packages (querying packagist).
 * 
 * @author Daniel Pozzi <bonndan76@googlemail.com>
 */
class PackageFactory
{
    /**
     * packagist api client
     * @var \Packagist\Api\Client
     */
    private $client;
    
    /**
     * nested metainfo factory.
     * 
     * @var \Metagist\ServerBundle\Entity\MetainfoFactory
     */
    private $metainfoFactory;
    
    /**
     * nested dependency factory.
     * 
     * @var \Metagist\ServerBundle\Entity\DependencyFactory
     */
    private $dependencyFactory;
    
    /**
     * Constructor.
     * 
     * @param \Packagist\Api\Client $client
     */
    public function __construct(PackagistClient $client, MetainfoFactory $metainfoFactory)
    {
        $this->client            = $client;
        $this->metainfoFactory   = $metainfoFactory;
        $this->dependencyFactory = new DependencyFactory();
    }
    
    /**
     * Fetches a package from packagist.
     * 
     * @param string $author
     * @param string $name
     * @return Package
     */
    public function byAuthorAndName($author, $name)
    {
        $identifier = $author . '/' . $name;
        $package = $this->createPackageFromPackagist($identifier);
        return $package;
    }
    
    /**
     * Creates an intermediate package by querying packagist.
     * 
     * @param string $identifier
     * @throws \Metagist\Api\Exception
     * @return Package
     */
    protected function createPackageFromPackagist($identifier)
    {
        /* @var $packagistPackage \Packagist\Api\Result\Package */
        try {
            $packagistPackage = $this->client->get($identifier);
        } catch (Guzzle\Common\Exception\RuntimeException $exception) {
            throw new Exception(
                'Guzzle exception: ' .$exception->getMessage(),
                Exception::APPLICATION_EXCEPTION,
                $exception
            );
        } catch (\Guzzle\Http\Exception\ClientErrorResponseException $exception) {
            throw new Exception('Could not find ' . $identifier . ' at packagist', Exception::PACKAGE_NOT_FOUND, $exception);
        }
        
        $package = new Package($packagistPackage->getName());
        $package->setDescription($packagistPackage->getDescription());
        $package->setType($packagistPackage->getType());
        
        //store version info
        $versions = array();
        foreach ($packagistPackage->getVersions() as $version) {
            $versions[] = $version->getVersion();
        }
        $package->setVersions($versions);
        
        $metainfos = $this->metainfoFactory->fromPackagistPackage($packagistPackage);
        $package->setMetaInfos($metainfos);
        
        $dependencies = $this->dependencyFactory->fromPackagistPackage($packagistPackage);
        foreach ($dependencies as $dep) {
            $dep->setPackage($package);
        }
        $package->setDependencies($dependencies);
        
        return $package;
    }
}