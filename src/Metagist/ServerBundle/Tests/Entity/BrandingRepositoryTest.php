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

    public function testCompilesCss()
    {
        $tempDir = sys_get_temp_dir();
        @unlink($tempDir . '/metagist.css');
        $this->repo->compileAllToCss($tempDir, $tempDir);
        
        $this->assertFileExists($tempDir . '/brandings.css');
        $contents = file_get_contents($tempDir . '/brandings.css');
        $this->assertContains('.test1', $contents);
        $this->assertContains('.test1 a', $contents);
        $this->assertContains('.test2', $contents);
        $this->assertContains('.test2 a', $contents);
    }
}