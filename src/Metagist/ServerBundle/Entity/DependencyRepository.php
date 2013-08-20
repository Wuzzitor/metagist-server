<?php
namespace Metagist\ServerBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Repository for package dependencies.
 * 
 * @author Daniel Pozzi <bonndan76@googlemail.com>
 */
class DependencyRepository extends EntityRepository
{
    /**
     * Retrieves all dependencies info for the given package.
     * 
     * @param \Metagist\Package $package
     * @return \Doctrine\Common\Collections\Collection
     */
    public function byPackage(Package $package)
    {
        $result = $this->findBy(array('package' => $package));
        return new ArrayCollection($result);
    }
}