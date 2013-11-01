<?php
namespace Metagist\WorkerBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Metagist\ServerBundle\Entity\Package;

/**
 * Command to retrieve metainfos on a package.
 * 
 * @author Daniel Pozzi <bonndan76@googlemail.com>
 */
class ScanCommand extends BaseCommand
{
    const COMMAND = 'mg:worker:scan';
    
    protected function configure()
    {
        $this
            ->setName('mg:worker:scan')
            ->setDescription('Scan package metainfos')
            ->addArgument('package', InputArgument::REQUIRED, 'Package indentifier (like test/abc)?')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $parts   = Package::splitIdentifier($input->getArgument('package'));
        $package = $this->getServiceProvider()->getPackage($parts[0], $parts[1]);
        
        $this->enableConsoleLogOutput();
        $logger  = $this->getServiceProvider()->logger();
        $scanner = new \Metagist\WorkerBundle\Scanner\PackageScanner($logger);
        $scanner->addScanner(new \Metagist\WorkerBundle\Scanner\Packagist($logger));
        $scanner->addScanner($this->createGithubScanner());
        $metainfos   = $scanner->scan($package);
        
        $metainfoRepo = $this->getServiceProvider()->metainfo();
        $metainfoRepo->disableSecurity();
        
        foreach ($metainfos as $metaInfo) {
            $metainfoRepo->save($metaInfo);
        }
    }
    
    /**
     * Creates the github pages scanner.
     * 
     * @return \Metagist\WorkerBundle\Scanner\GitHub
     */
    private function createGithubScanner()
    {
        $clientId     = $this->getContainer()->getParameter('metagist.github.client.id');
        $clientSecret = $this->getContainer()->getParameter('metagist.github.client.secret');
        
        $githubClient = \Metagist\WorkerBundle\Scanner\GitHub::createGithubClient($clientId, $clientSecret);
        $scanner = new \Metagist\WorkerBundle\Scanner\GitHub($this->getServiceProvider()->logger(), $githubClient);
        return $scanner;
    }
}