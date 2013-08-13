<?php
namespace Metagist\ServerBundle\Tests\Controller;

use Metagist\ServerBundle\Controller\ServiceProvider;

/**
 * Tests the metagist application
 * 
 * @author Daniel Pozzi <bonndan76@googlemail.com>
 */
class ServiceProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * system under test
     * 
     * @var \Metagist\ServerBundle\Controller\ServiceProvider
     */
    private $serviceProvider;
    
    private $containerMock;
    
    /**
     * Test setup.
     */
    public function setUp()
    {
        parent::setUp();
        $this->containerMock = $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');
        $this->serviceProvider = new ServiceProvider($this->containerMock);
    }
    
    /**
     * Session shortcut test
     */
    public function testProvidesSessionShortcut()
    {
        $this->containerMock->expects($this->once())
            ->method('get')
            ->with('session');
        $this->serviceProvider->session();
    }
    
    /**
     * monolog shortcut test
     */
    public function testProvidesMonologShortcut()
    {
        $this->containerMock->expects($this->once())
            ->method('get')
            ->with('logger');
        $this->serviceProvider->logger();
    }
    
    /**
     * packge repo shortcut test
     */
    public function testProvidesPackageRepoShortcut()
    {
        $this->createDoctrineMock('MetagistServerBundle:Package');
        $this->serviceProvider->packages();
    }
    
    /**
     * 
     * @param type $entityName
     */
    protected function createDoctrineMock($entityName)
    {
        $em = $this->getMockBuilder("\Doctrine\ORM\EntityManager")
            ->disableOriginalConstructor()
            ->getMock();
        $em->expects($this->once())
            ->method('getRepository')
            ->with($entityName)
            ->with($this->getMock("\Doctrine\ORM\EntityRepository"));
        $registry = $this->getMockBuilder("\Doctrine\Bundle\DoctrineBundle\Registry")
            ->disableOriginalConstructor()
            ->getMock();
        $registry->expects($this->once())
            ->method('getManager')
            ->will($this->returnValue($em));
        
        $this->containerMock->expects($this->once())
            ->method('get')
            ->with('doctrine')
            ->will($this->returnValue($em));
    }
    
    /**
     * metainfo repo shortcut test
     */
    public function testMetaInfoRepoShortcutReturnsProxy()
    {
        $this->createDoctrineMock("MetagistServerBundle:Metainfo");
        
        $this->containerMock->expects($this->once())
            ->method('get')
            ->with('security')
            ->will($this->returnValue($this->getMock("\Symfony\Component\Security\Core\SecurityContextInterface")));
        
        $cat = $this->getMockBuilder("\Metagist\CategorySchema")
                ->disableOriginalConstructor()
                ->getMock();
        $this->containerMock->expects($this->once())
            ->method('get')
            ->with('metagist.categoryschema')
            ->will($this->returnValue($cat));
        
        $this->assertInstanceOf("\Metagist\MetaInfoRepositoryProxy", $this->serviceProvider->metainfo());
    }
    
    /**
     * rating repo shortcut test
     */
    public function testProvidesRatingRepoShortcut()
    {
        $this->createDoctrineMock("MetagistServerBundle:Rating");
        $this->serviceProvider->ratings();
    }
    
    /**
     * schema shortcut test
     */
    public function testProvidesCategorySchemaShortcut()
    {
        $cat = $this->getMockBuilder("\Metagist\CategorySchema")
                ->disableOriginalConstructor()
                ->getMock();
        $this->containerMock->expects($this->once())
            ->method('get')
            ->with('metagist.categoryschema')
            ->will($this->returnValue($cat));
        
        $this->serviceProvider->categories();
    }
    
    /**
     * security shortcut test
     */
    public function testProvidesSecurityShortcut()
    {
        $this->containerMock->expects($this->once())
            ->method('get')
            ->with('security');
        $this->serviceProvider->security();
    }
    
    /**
     * Ensures the application returns the api provider
     */
    public function testGetApi()
    {
        $this->containerMock->expects($this->once())
            ->method('get')
            ->with('metagist.api');
        $this->serviceProvider->getApi();
    }
}