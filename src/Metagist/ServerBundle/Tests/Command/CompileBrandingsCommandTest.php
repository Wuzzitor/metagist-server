<?php
namespace Metagist\ServerBundle\Tests\Command;

use Metagist\ServerBundle\Tests\WebDoctrineTestCase;
use Metagist\ServerBundle\Command\CompileBrandingsCommand;
use Metagist\ServerBundle\Entity\Branding;

/**
 * 
 * Tests the branding repo.
 * 
 * @author Daniel Pozzi <bonndan76@googlemail.com>
 */
class CompileBrandingsCommandTest extends WebDoctrineTestCase
{
    /**
     * system under test
     * 
     * @var \Metagist\ServerBundle\Command\CompileBrandingsCommand
     */
    private $command;
    
    /**
     * Test setup
     */
    public function setUp()
    {
        parent::setUp();
        $this->command = new CompileBrandingsCommand();
        $this->command->setContainer(self::$client->getContainer());
    }
    
    protected function loadFixtures()
    {
        $branding = new Branding();
        $branding->setVendor('test1');
        $branding->setLess('a{color:black}');
        $this->entityManager->persist($branding);
        
        $branding = new Branding();
        $branding->setVendor('test2');
        $branding->setLess('a{color:white}');
        $this->entityManager->persist($branding);

        $this->entityManager->flush();
    }

    public function testGetLess()
    {
        $less = $this->command->getLess();
        $this->assertContains('.test1{' . PHP_EOL . 'a{', $less);
        $this->assertContains('.test2{' . PHP_EOL . 'a{', $less);
    }
    
    public function testWriteLess()
    {
        $tempDir = sys_get_temp_dir();
        @unlink($tempDir . '/brandings.less');
        $this->command->writeLess($tempDir);
        
        $this->assertFileExists($tempDir . '/brandings.less');
        $contents = file_get_contents($tempDir . '/brandings.less');
        $this->assertContains('.test1{' . PHP_EOL . 'a{', $contents);
        $this->assertContains('.test2{' . PHP_EOL . 'a{', $contents);
    }
    
    public function testLessCompilerIntegration()
    {
        $tempDir = sys_get_temp_dir();
        $targetPath = $tempDir . '/test.css';
        @unlink($tempDir . '/brandings.less');
        @unlink($targetPath);
        
        $lessFile = $this->command->writeLess($tempDir);
        
        $res = $this->command->compileBrandings($lessFile, $targetPath);
        $this->assertTrue($res);
        $this->assertFileExists($targetPath);
    }
}