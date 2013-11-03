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
        $versions = $package->getVersions();
        /* @var $firstVersion \Packagist\Api\Result\Package\Version */
        $firstVersion = current($versions);

        if ($firstVersion != false) {
            $version = $firstVersion->getVersion();

            /*
             * normal requirements
             */
            $dependencies = array_merge(
                $dependencies,
                $this->getDependenciesFromRequirements($firstVersion->getRequire(), $version)
            );
            
            /*
             * dev requirements
             */
            $dependencies = array_merge(
                $dependencies,
                $this->getDependenciesFromRequirements($firstVersion->getRequireDev(), $version)
            );
        }
        
        return $dependencies;
    }

    /**
     * Extract dependencies.
     * 
     * @param array $dependencyEntries
     * @param string $versionString
     * @return \Metagist\ServerBundle\Entity\Dependency[]
     */
    private function getDependenciesFromRequirements($dependencyEntries, $versionString)
    {
        $dependencies = array();
        if (!$dependencyEntries) {
            return $dependencies;
        }
        
        foreach ($dependencyEntries as $identifier => $version) {
            $dependency = new Dependency();
            $dependency->setPackageVersion($versionString);
            $dependency->setDependencyIdentifier($identifier);
            $dependency->setDependencyVersion($version);
            $dependency->setIsDevDependency(true);
            $dependencies[] = $dependency;
        }
        return $dependencies;
    }

}
