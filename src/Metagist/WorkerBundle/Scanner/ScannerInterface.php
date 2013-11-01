<?php
namespace Metagist\WorkerBundle\Scanner;

use Metagist\ServerBundle\Entity\Package;

/**
 * Interface for metainfo providers.
 * 
 * @author Daniel Pozzi <bonndan76@googlemail.com>
 */
interface ScannerInterface
{
    /**
     * Returns a collection of retrieved metainfos.
     * 
     * @param \Metagist\ServerBundle\Entity\Package $package
     * @return \Metagist\ServerBundle\Entity\Metainfo[]
     */
    public function scan(Package $package);
}