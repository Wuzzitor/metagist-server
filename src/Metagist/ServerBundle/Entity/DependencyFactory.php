<?php
namespace Metagist\ServerBundle\Entity;

use Metagist\ServerBundle\Entity\Dependency;

/**
 * Factory for Dependency objects.
 * 
 * This factory is only used on initial package creation.
 * 
 * @author Daniel Pozzi <bonndan76@googlemail.com>
 */
class DependencyFactory
{
    /**
     * Creates dependencies based on a packagist package object.
     * 
     * @param \Packagist\Api\Result\Package $package
     * @return Dependency[]
     */
    public function fromPackagistPackage(\Packagist\Api\Result\Package $package)
    {
        $dependencies = array();
        $versions     = $package->getVersions();
        /* @var $firstVersion \Packagist\Api\Result\Package\Version */
        $firstVersion  = current($versions);
        
        if ($firstVersion != false) {
            $versionString = $firstVersion->getVersion();
            $dependencyEntries = $firstVersion->getRequire();
            foreach ($dependencyEntries as $entry) {
                $dependency = new Dependency();
                $dependency->setPackageVersion($versionString);
                $dependency->setDependencyIdentifier($entry);
                $dependencies[] = $dependency;
            }
        }
        
        return $dependencies;
    }
}
