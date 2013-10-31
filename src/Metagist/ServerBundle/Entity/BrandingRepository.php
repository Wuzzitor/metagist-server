<?php

namespace Metagist\ServerBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * BrandingRepository
 *
 */
class BrandingRepository extends EntityRepository
{
    /**
     * Compiles all branding less into css.
     * 
     * @param string $sourceDir
     * @return string the less file path
     * @todo move out of repo
     */
    public function compileAllToLess($sourceDir)
    {
        if (!is_dir($sourceDir)) {
            throw new \InvalidArgumentException($sourceDir . ' is not a dir.');
        }
        
        $all = $this->findAll();
        $lessFile = $sourceDir . '/brandings.less';
        $handle = fopen($lessFile, 'w+');
        foreach ($all as $branding) {
            /* @var $branding \Metagist\ServerBundle\Entity\Branding */
            fwrite($handle, $branding->getLessWithVendor());
        }
        fclose($handle);
        
        return $lessFile;
    }
}
