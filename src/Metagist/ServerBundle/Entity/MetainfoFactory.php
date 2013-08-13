<?php
namespace Metagist\ServerBundle\Entity;

use \Doctrine\Common\Collections\ArrayCollection;
use Metagist\ServerBundle\Entity\Metainfo;

/**
 * Factory for Metainfo objects.
 * 
 * This factory is onyl used on package creation, updates are transmitted by the
 * worker.
 * 
 * @author Daniel Pozzi <bonndan76@googlemail.com>
 */
class MetainfoFactory
{
    /**
     * logger instance.
     * 
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;
    
    /**
     * Constructor requires a logger instance.
     * 
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(\Psr\Log\LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
    
    /**
     * Creates metainfos based on a packagist package object.
     * 
     * @param \Packagist\Api\Result\Package $package
     * @return \Doctrine\Common\Collections\Collection
     */
    public function fromPackagistPackage(\Packagist\Api\Result\Package $package)
    {
        $metainfos     = array();
        $versions      = $package->getVersions();
        /* @var $firstVersion \Packagist\Api\Result\Package\Version */
        $firstVersion  = current($versions);
        
        if ($firstVersion != false) {
            $versionString = $firstVersion->getVersion();
            $repository    = $package->getRepository();
            $metainfos = new ArrayCollection(
                array(
                    Metainfo::fromValue(Metainfo::REPOSITORY, $repository, $versionString),
                    Metainfo::fromValue(Metainfo::HOMEPAGE, $firstVersion->getHomepage(), $versionString),
                    Metainfo::fromValue(Metainfo::MAINTAINERS, count($package->getMaintainers()), $versionString),
                    Metainfo::fromValue(Metainfo::REQUIRES, count($firstVersion->getRequire()), $versionString),
                    Metainfo::fromValue(Metainfo::REQUIRES_DEV, count($firstVersion->getRequireDev()), $versionString)
                )
            );
            
            $licenses = $firstVersion->getLicense();
            if (is_array($licenses)) {
                $metainfos->add(Metainfo::fromValue(Metainfo::LICENSE, implode(' ', $licenses), $versionString));
            }
        }
        
        return $metainfos;
    }
}
