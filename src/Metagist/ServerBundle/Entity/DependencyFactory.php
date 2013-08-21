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
            if (!$dependencyEntries) {
                $dependencyEntries = array();
            }
            
            /*
             * entry is like array('php' => string '>=5.3.3')
             */
            foreach ($dependencyEntries as $identifier => $version) {
                $dependency = new Dependency();
                $dependency->setPackageVersion($versionString);
                $dependency->setDependencyIdentifier($identifier);
                $dependency->setDependencyVersion($version);
                $dependencies[] = $dependency;
            }
            
            $dependencyEntries = $firstVersion->getRequireDev();
            if (!$dependencyEntries) {
                $dependencyEntries = array();
            }
            
            foreach ($dependencyEntries as $identifier => $version) {
                $dependency = new Dependency();
                $dependency->setPackageVersion($versionString);
                $dependency->setDependencyIdentifier($identifier);
                $dependency->setDependencyVersion($version);
                $dependency->setIsDevDependency(true);
                $dependencies[] = $dependency;
            }
        }
        
        return $dependencies;
    }
}
