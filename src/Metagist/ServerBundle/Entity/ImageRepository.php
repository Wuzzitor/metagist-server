<?php
namespace Metagist\ServerBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Metagist\ServerBundle\Entity\Image;

/**
 * Repository for package images.
 * 
 * @author Daniel Pozzi <bonndan76@googlemail.com>
 */
class ImageRepository extends EntityRepository
{
    /**
     * Retrieves all dependencies info for the given package.
     * 
     * @param \Metagist\Package $package
     * @return \Metagist\ServerBundle\Entity\Image
     */
    public function byPackage(Package $package)
    {
        $image = $this->findOneBy(array('package' => $package));
        if (!$image) {
            $image = new Image();
            $image->setPackage($package);
        }
        
        return $image;
    }
}