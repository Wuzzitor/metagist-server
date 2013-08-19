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
     * Constructor.
     * 
     * @param \Packagist\Api\Client $client
     */
    public function __construct(PackagistClient $client, MetainfoFactory $metainfoFactory)
    {
        $this->client          = $client;
        $this->metainfoFactory = $metainfoFactory;
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
     * @throws Exception
     * @return Package
     */
    protected function createPackageFromPackagist($identifier)
    {
        /* @var $packagistPackage \Packagist\Api\Result\Package */
        try {
            $packagistPackage = $this->client->get($identifier);
        } catch (\Guzzle\Http\Exception\ClientErrorResponseException $exception) {
            throw new \Metagist\Api\Exception('Could not find ' . $identifier . ' at packagist', Exception::PACKAGE_NOT_FOUND, $exception);
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
        
        return $package;
    }
}