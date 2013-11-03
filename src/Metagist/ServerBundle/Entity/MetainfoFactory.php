<?php
namespace Metagist\ServerBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Metagist\WorkerBundle\Scanner\Packagist;
use Psr\Log\LoggerInterface;
use Packagist\Api\Result\Package as PackagistPackage;

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
     * @var LoggerInterface
     */
    protected $logger;
    
    /**
     * Constructor requires a logger instance.
     * 
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
    
    /**
     * Creates metainfos based on a packagist package object.
     * 
     * @param Package $package
     * @return Collection
     */
    public function fromPackagistPackage(PackagistPackage $package)
    {
        return new ArrayCollection(
            Packagist::fromPackagistPackage($package)
        );
    }    
}
