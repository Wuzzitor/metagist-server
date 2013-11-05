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
        $this->enableConsoleLogOutput();
        $sourceDir = $this->getContainer()->get('kernel')->getCacheDir();
        $targetPath = $this->getContainer()->get('kernel')->getRootDir() . '/../web/css/brandings.css';

        if ($this->compileBrandings($sourceDir, $targetPath)) {
            $this->getServiceProvider()->logger()->addError('Error compiling the brandings.');
        } else {
            $this->getServiceProvider()->logger()->addInfo('OK.');
        }
    }

    /**
     * Compiles the less.
     * 
     * @param string $lessFile
     * @param string $targetPath
     * @return boolean
     */
    public function compileBrandings($lessFile, $targetPath)
    {
        $lessComp = new \lessc();
        if (!file_put_contents($targetPath, $lessComp->compileFile($lessFile))) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Compiles all branding less into css.
     * 
     * @param string $sourceDir
     * @return string the less file path
     */
    public function writeLess($sourceDir)
    {
        if (!is_dir($sourceDir)) {
            throw new \InvalidArgumentException($sourceDir . ' is not a dir.');
        }

        $lessFile = $sourceDir . '/brandings.less';
        if (file_put_contents($lessFile, $this->getLess())) {
            return $lessFile;
        }
    }

    /**
     * Returns the concatenated less for the brandings..
     * 
     * @return string the less file content
     */
    public function getLess()
    {
        $buffer = '';
        foreach ($this->getBrandingRepo()->findAll() as $branding) {
            /* @var $branding \Metagist\ServerBundle\Entity\Branding */
            $buffer .= $branding->getLessWithVendor();
        }
        return $buffer;
    }

    /**
     * Returns the branding repo.
     * 
     * @return \Metagist\ServerBundle\Entity\BrandingRepository
     */
    private function getBrandingRepo()
    {
        $em = $this->getContainer()->get('doctrine')->getManager();
        return $em->getRepository('MetagistServerBundle:Branding');
    }

}
