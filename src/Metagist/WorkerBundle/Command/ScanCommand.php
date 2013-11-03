<?php
namespace Metagist\WorkerBundle\Command;

use Metagist\ServerBundle\Entity\Package;
use Metagist\WorkerBundle\Scanner\GitHub;
use Metagist\WorkerBundle\Scanner\PackageScanner;
use Metagist\WorkerBundle\Scanner\Packagist;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

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
        $scanner = new PackageScanner($logger);
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
     * @return GitHub
     */
    private function createGithubScanner()
    {
        $clientId     = $this->getContainer()->getParameter('metagist.github.client.id');
        $clientSecret = $this->getContainer()->getParameter('metagist.github.client.secret');
        
        $githubClient = GitHub::createGithubClient($clientId, $clientSecret);
        $scanner = new GitHub($this->getServiceProvider()->logger(), $githubClient);
        return $scanner;
    }
}