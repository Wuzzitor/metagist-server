<?php
namespace Metagist\WorkerBundle\Scanner;

use \Doctrine\Common\Collections\ArrayCollection;
use \Packagist\Api\Client as PackagistClient;
use Metagist\ServerBundle\Entity\Package;
use Metagist\ServerBundle\Entity\Metainfo;

/**
 * Scanner for packagist.
 * 
 * @author Daniel Pozzi <bonndan76@googlemail.com>
 */
class Packagist extends Base implements ScannerInterface
{
    /**
     * packagist api client
     * 
     * @var PackagistClient 
     */
    private $packagistClient;
    
    /**
     * Fetches metainfos from a packagist package.
     * 
     * @param Package $package
     * @return \Metagist\ServerBundle\Entity\Metainfo[]
     */
    public function scan(Package $package)
    {
        $packagistPackage = $this->getPackagistClient()->get($package->getIdentifier());
        return self::fromPackagistPackage($packagistPackage);
    }

    /**
     * Creates metainfos based on a packagist package object.
     * 
     * @param \Packagist\Api\Result\Package $package
     * @return \Doctrine\Common\Collections\Collection
     */
    public static function fromPackagistPackage(\Packagist\Api\Result\Package $package)
    {
        $metainfos = array();
        $versions      = $package->getVersions();
        /* @var $firstVersion \Packagist\Api\Result\Package\Version */
        $firstVersion  = current($versions);
        
        if ($firstVersion == false) {
            return $metainfos;
        }
        
        $version = $firstVersion->getVersion();
        
        $metainfos[] = Metainfo::fromValue(Metainfo::REPOSITORY, $package->getRepository(), $version);
        $metainfos[] = Metainfo::fromValue(Metainfo::HOMEPAGE, $firstVersion->getHomepage(), $version);
        $metainfos[] = Metainfo::fromValue(Metainfo::MAINTAINERS, count($package->getMaintainers()), $version);
        $metainfos[] = Metainfo::fromValue(Metainfo::PACKAGIST_FAVERS, $package->getFavers(), $version);
        if ($package->getDownloads()) {
            $metainfos[] = Metainfo::fromValue(Metainfo::PACKAGIST_DOWNLOADS, $package->getDownloads()->getTotal(), $version);
        }

        $licenses = $firstVersion->getLicense();
        if (is_array($licenses)) {
            $metainfos[] = Metainfo::fromValue(Metainfo::LICENSE, implode(' ', $licenses), $version);
        }
        
        return $metainfos;
    }
    
    /**
     * Inject the packagist client.
     * 
     * @param \Packagist\Api\Client $client
     */
    public function setPackagistClient(PackagistClient $client)
    {
        $this->packagistClient = $client;
    }
    
    /**
     * Returns the packagist api client.
     * 
     * @return \Packagist\Api\Client
     */
    protected function getPackagistClient()
    {
        if ($this->packagistClient === null) {
            $client = new PackagistClient();
            return $client;
        }
        
        return $this->packagistClient;
    }
}
