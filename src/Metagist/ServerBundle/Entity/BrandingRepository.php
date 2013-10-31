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
     * @param string $targetDir
     * @throws \Metagist\Exception
     * @todo move out of repo
     */
    public function compileAllToCss($sourceDir, $targetDir)
    {
        if (!is_dir($sourceDir)) {
            throw new \InvalidArgumentException($sourceDir . ' is not a dir.');
        }
        
        if (!is_dir($targetDir)) {
            throw new \InvalidArgumentException($targetDir . ' is not a dir.');
        }
        $all = $this->findAll();
        $lessFile = $sourceDir . '/brandings.less';
        $handle = fopen($lessFile, 'w+');
        foreach ($all as $branding) {
            /* @var $branding \Metagist\ServerBundle\Entity\Branding */
            fwrite($handle, $branding->getLess());
        }
        fclose($handle);
        
        $output = system('lessc ' . $lessFile . ' ' . $targetDir . '/brandings.css', $returnVal);
        
        if ($returnVal != 0) {
            throw new \Metagist\Exception('Less compilation failed:  ' . $output);
        }
    }
}
