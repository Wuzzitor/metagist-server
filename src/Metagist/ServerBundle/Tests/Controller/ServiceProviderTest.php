<?php
namespace Metagist\ServerBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Metagist\ServerBundle\Controller\ServiceProvider;

/**
 * Tests the metagist application
 * 
 * @author Daniel Pozzi <bonndan76@googlemail.com>
 */
class ServiceProviderTest extends WebTestCase
{
    /**
     * system under test
     * 
     * @var \Metagist\ServerBundle\Controller\ServiceProvider
     */
    private $serviceProvider;
    
    /**
     * Test setup.
     */
    public function setUp()
    {
        parent::setUp();
        $kernel = self::createKernel();
        $kernel->boot();
        $this->serviceProvider = new ServiceProvider($kernel->getContainer());
    }
    
    /**
     * Session shortcut test
     */
    public function testProvidesSessionShortcut()
    {
        $this->serviceProvider->session();
    }
    
    /**
     * monolog shortcut test
     */
    public function testProvidesMonologShortcut()
    {
        $this->serviceProvider->logger();
    }
    
    /**
     * packge repo shortcut test
     */
    public function testProvidesPackageRepoShortcut()
    {
        $this->serviceProvider->packages();
    }
    
    /**
     * metainfo repo shortcut test
     */
    public function testMetaInfoRepoShortcutReturnsProxy()
    {
        $this->assertInstanceOf("\Metagist\ServerBundle\Entity\MetaInfoRepositoryProxy", $this->serviceProvider->metainfo());
    }
    
    /**
     * rating repo shortcut test
     */
    public function testProvidesRatingRepoShortcut()
    {
        $this->serviceProvider->ratings();
    }
    
    /**
     * schema shortcut test
     */
    public function testProvidesCategorySchemaShortcut()
    {
        $cat = $this->serviceProvider->categories();
        $this->assertInstanceOf("\Metagist\CategorySchema", $cat);
    }
    
    /**
     * security shortcut test
     */
    public function testProvidesSecurityShortcut()
    {
        $this->serviceProvider->security();
    }
    
    /**
     * Ensures the application returns the api provider
     */
    public function testGetApi()
    {
        $this->markTestSkipped('ApiProvider must be refactored to be usable with symfony');
        $this->serviceProvider->getApi();
    }
}