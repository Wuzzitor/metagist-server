<?php
namespace Metagist\WorkerBundle\Scanner;

use Metagist\ServerBundle\Entity\Package;
use Metagist\WorkerBundle\Exception;

/**
 * Scanner decorator.
 * 
 * @author Daniel Pozzi <bonndan76@googlemail.com>
 */
class PackageScanner extends Base implements ScannerInterface
{
    /**
     * enabled scanners
     * 
     * @var ScannerInterface[]
     */
    private $scanners = array();
    
    /**
     * Returns a collection of retrieved metainfos.
     * 
     * @param Package $package
     * @return \Metagist\MetaInfo[]
     */
    public function scan(Package $package)
    {
        $metaInfos = array(); 
        
        foreach ($this->scanners as $scanner) {
            $retrievedInfos = $scanner->scan($package);
            if (is_array($retrievedInfos)) {
                $metaInfos = array_merge($metaInfos, $retrievedInfos);
            }
        }
        foreach ($metaInfos as $metaInfo) {
            $metaInfo->setPackage($package);
        }
        
        $this->logger->info('Retrieved ' . count($metaInfos) . ' infos.');
        return $metaInfos;
    }
    
    /**
     * Add a scanner.
     * 
     * @param ScannerInterface $scanner
     */
    public function addScanner(ScannerInterface $scanner)
    {
        $this->scanners[] = $scanner;
    }
}