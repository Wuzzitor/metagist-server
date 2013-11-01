<?php
/**
 * FeedReader.php
 * 
 * @author Daniel Pozzi <bonndan76@googlemail.com>
 */

namespace Metagist\WorkerBundle;

use Psr\Log\LoggerInterface;
use Zend\Feed\Reader\Feed\FeedInterface;
use Zend\Feed\Reader\Entry\EntryInterface;
use Metagist\WorkerBundle\Scanner\PackageScanner;
use Metagist\WorkerBundle\Command\ScanCommand;
use Metagist\ServerBundle\Entity\Package;
use Metagist\ServerBundle\Validation\Validator;
use JMS\JobQueueBundle\Entity\Job;

/**
 * Class which scans packages based on packagist feed updates.
 * 
 * @author Daniel Pozzi <bonndan76@googlemail.com>
 */
class FeedReader
{
    /**
     * Feed
     * 
     * @var \Zend\Feed\Reader\Feed\FeedInterface 
     */
    private $feed;
    
    /**
     * logger instance
     * 
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;
    
    /**
     * Constructor.
     * 
     * @param \Zend\Feed\Reader\Feed\FeedInterface $feed
     * @param PackageScanner $scanner
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(FeedInterface $feed, LoggerInterface $logger = null)
    {
        $this->feed    = $feed;
        $this->logger  = $logger;
    }
    
    /**
     * Read the latest feed entries.
     * 
     * @return Package[]
     */
    public function read()
    {
        $packages = array();
        foreach ($this->feed as $entry) {
            $package = $this->createDummyPackageFromEntry($entry);
            if ($package === null) {
                $this->logger && $this->getLogger()->warning('Feed: Skipped entry ' . $entry->getTitle());
                continue;
            }
            $packages[] = $package;
        }
        
        return $packages;
    }
    
    /**
     * Creates a dummy package.
     * 
     * @param \Zend\Feed\Reader\Entry\EntryInterface $entry
     * @return Package|null
     */
    protected function createDummyPackageFromEntry(EntryInterface $entry)
    {
        $title      = $entry->getTitle();
        $tmp        = explode(' ', $title);
        $identifier = $tmp[0];
        $version    = trim($tmp[1], '()');
        
        if (!Validator::isValidIdentifier($identifier)) {
            return null;
        }
        
        $package = new Package($identifier);
        $package->setVersions(array($version));
        return $package;
    }
}