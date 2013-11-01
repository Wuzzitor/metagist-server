<?php
namespace Metagist\WorkerBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use JMS\JobQueueBundle\Entity\Job;

use Metagist\WorkerBundle\FeedReader;

/**
 * Command for a cronjobs which regularly reads the packagist feed.
 * 
 * 
 */
class FollowFeedCommand extends BaseCommand
{
    /**
     * packagist rss feed.
     * 
     * @var string 
     */
    private $feed = 'https://packagist.org/feeds/releases.rss';
    
    /**
     * Set a different feed.
     * 
     * @param string $feed
     */
    public function setFeed($feed)
    {
        $this->feed = $feed;
    }
    
    protected function configure()
    {
        $this
            ->setName('mg:worker:follow-feed')
            ->setDescription('Follow the packagist.org feed.')
        ;
    }

    /**
     * Foreach feed entry a scan job is added to the queue.
     * 
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->enableConsoleLogOutput();
        $reader = new FeedReader($this->createFeed(), $this->getServiceProvider()->logger());
        $dummyPackages = $reader->read();
        $entityManager = $this->getContainer()->get('doctrine')->getEntityManager();
        
        foreach ($dummyPackages as $package) {
            $job = new Job(ScanCommand::COMMAND, array($package->getIdentifier()));
            $entityManager->persist($job);
        }
        $entityManager->flush($job);
    }
    
    /**
     * Creates a feed reader.
     * 
     * @return \Zend\Feed\Reader\Feed\FeedInterface
     * @link http://framework.zend.com/manual/2.0/en/modules/zend.cache.storage.adapter.html#the-filesystem-adapter
     */
    protected function createFeed()
    {
        $cacheDir = $this->getContainer()->get('kernel')->getCacheDir();
        $cache = \Zend\Cache\StorageFactory::adapterFactory('Filesystem', array('cache_dir' => $cacheDir));
        \Zend\Feed\Reader\Reader::setCache($cache);
        \Zend\Feed\Reader\Reader::useHttpConditionalGet();
        $adapter = new \Zend\Http\Client\Adapter\Curl();
        $client = \Zend\Feed\Reader\Reader::getHttpClient();
        $client->setAdapter($adapter);
        
        $cert = realpath(__DIR__ . '/../Resources/config/packagist.org.pem');
        $adapter->setOptions(array(
            'curloptions' => array(
                CURLOPT_CAPATH => dirname($cert),
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_CAINFO => $cert,
            )
        ));

        return \Zend\Feed\Reader\Reader::import($this->feed);
    }
}