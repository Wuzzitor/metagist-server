<?php
namespace Metagist\ServerBundle\Tests\Controller;

use Metagist\ServerBundle\Tests\WebDoctrineTestCase;
use Metagist\ServerBundle\Controller\ServiceProvider;

/**
 * Tests the metagist application
 * 
 * @author Daniel Pozzi <bonndan76@googlemail.com>
 */
class ServiceProviderTest extends WebDoctrineTestCase
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
        $this->serviceProvider = new ServiceProvider(static::$client->getContainer());
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
     * dep repo shortcut test
     */
    public function testProvidesDependencyRepoShortcut()
    {
        $this->serviceProvider->dependencies();
    }
    
    /**
     * schema shortcut test
     */
    public function testProvidesCategorySchemaShortcut()
    {
        $cat = $this->serviceProvider->categories();
        $this->assertInstanceOf("\Metagist\ServerBundle\Validation\CategorySchema", $cat);
    }
    
    /**
     * security shortcut test
     */
    public function testProvidesSecurityShortcut()
    {
        $this->serviceProvider->security();
    }
    
    /**
     * 
     */
    public function testGetPackage()
    {
        $this->setExpectedException("\Metagist\ServerBundle\Exception");
        $this->serviceProvider->getPackage('cannot', 'befound');
    }
    
    public function testGetPackagistApiClient()
    {
        $this->assertInstanceOf("\Packagist\Api\Client", $this->serviceProvider->getPackagistApiClient());
    }
}