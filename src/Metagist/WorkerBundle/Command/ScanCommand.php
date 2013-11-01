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
        
        $scanner = new \Metagist\WorkerBundle\Scanner\PackageScanner($this->getServiceProvider()->logger());
        $infos   = $scanner->scan($package);
    }
}