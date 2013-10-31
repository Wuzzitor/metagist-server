<?php
namespace Metagist\ServerBundle\Tests\Entity;

use Metagist\ServerBundle\Tests\WebDoctrineTestCase;
use Metagist\ServerBundle\Entity\BrandingRepository;
use Metagist\ServerBundle\Entity\Branding;

/**
 * 
 * Tests the branding repo.
 * 
 * @author Daniel Pozzi <bonndan76@googlemail.com>
 */
class BrandingRepositoryTest extends WebDoctrineTestCase
{
    /**
     * system under test
     * 
     * @var \Metagist\ServerBundle\Entity\BrandingRepository
     */
    private $repo;
    
    /**
     * Test setup
     */
    public function setUp()
    {
        parent::setUp();
        $this->repo = $this->entityManager->getRepository('MetagistServerBundle:Branding');
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

    public function testCompilesLess()
    {
        $tempDir = sys_get_temp_dir();
        @unlink($tempDir . '/brandings.less');
        $this->repo->compileAllToLess($tempDir);
        
        $this->assertFileExists($tempDir . '/brandings.less');
        $contents = file_get_contents($tempDir . '/brandings.less');
        $this->assertContains('.test1{' . PHP_EOL . 'a{', $contents);
        $this->assertContains('.test2{' . PHP_EOL . 'a{', $contents);
    }
}