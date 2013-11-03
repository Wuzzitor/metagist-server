<?php
/**
 * CompileBrandingsCommand.php
 * 
 * @package metagist-server
 * @author Daniel Pozzi <bonndan76@googlemail.com>
 */
namespace Metagist\ServerBundle\Command;

use Metagist\WorkerBundle\Command\BaseCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Updates the brandings.css file.
 * 
 * @package metagist-server
 * @author Daniel Pozzi <bonndan76@googlemail.com>
 */
class CompileBrandingsCommand extends BaseCommand
{
    protected function configure()
    {
        $this
            ->setName('mg:server:compile-brandings')
            ->setDescription('Compiles brandings from less to css.');
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('doctrine')->getManager();
        $repo = $em->getRepository('MetagistServerBundle:Branding');
        /* @var $repo \Metagist\ServerBundle\Entity\BrandingRepository */
        $sourceDir = $this->getContainer()->get('kernel')->getCacheDir();
        $lessFile = $repo->compileAllToLess($sourceDir);
        
        $targetPath = $this->getContainer()->get('kernel')->getRootDir() . '/../web/css/brandings.css';
        $lessComp   = new \lessc();
        
        $this->enableConsoleLogOutput();
        if (!file_put_contents($targetPath, $lessComp->compileFile($lessFile))) {
            $this->getServiceProvider()->logger()->addError('Error compiling the brandings.');
        } else {
            $this->getServiceProvider()->logger()->addInfo('OK.');
        }
    }
}
