<?php
namespace Metagist\ServerBundle\Tests\Entity;

use Metagist\ServerBundle\Entity\PackageFactory;
use Metagist\ServerBundle\Entity\Metainfo;

/**
 * Tests the package factory class.
 * 
 * @author Daniel Pozzi <bonndan76@googlemail.com>
 */
class PackageFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * system under test
     * @var PackageFactory
     */
    private $repo;
    
    /**
     * client mock
     * @var \Packagist\Api\Client
     */
    private $client;
    
    /**
     * mi factory mock
     * @var \Packagist\MetaInfoFactory 
     */
    private $metainfoFactory;
    
    /**
     * Test setup
     */
    public function setUp()
    {
        parent::setUp();
        $this->client = $this->getMockBuilder("\Packagist\Api\Client")
            ->disableOriginalConstructor()
            ->getMock();
        $this->metainfoFactory = $this->getMockBuilder("\Metagist\ServerBundle\Entity\MetainfoFactory")
            ->disableOriginalConstructor()
            ->getMock();
        $this->repo = new PackageFactory($this->client, $this->metainfoFactory);
    }
    
    
    /**
     * Ensures a package is returned if found.
     */
    public function testByAuthorAndName()
    {
        $collection = new \Doctrine\Common\Collections\ArrayCollection(array());
        $collection->add(Metainfo::fromValue('test/test', 1));
        
        $pp = $this->createPackage();
        $this->client->expects($this->once())
            ->method('get')
            ->with('author/name')
            ->will($this->returnValue($pp));
        $this->metainfoFactory->expects($this->once())
            ->method('fromPackagistPackage')
            ->will($this->returnValue($collection));
        
        $result = $this->repo->byAuthorAndName('author', 'name');
        $this->assertInstanceOf('\Metagist\ServerBundle\Entity\Package', $result);
        
        $metaInfos = $result->getMetaInfos();
        $this->assertNotEmpty($metaInfos);
        $this->assertInstanceOf("\Metagist\ServerBundle\Entity\MetaInfo", $metaInfos[0]);
        $this->assertInternalType('array', $result->getVersions());
        $this->assertEquals('description', $result->getDescription());
    }
    
    /**
     * Ensures an exception is thrown if the package is not found.
     */
    public function testThrowsMetagistException()
    {
        $this->client->expects($this->once())
            ->method('get')
            ->will($this->throwException(new \Guzzle\Http\Exception\ClientErrorResponseException('test')));
        
        $this->setExpectedException("\Metagist\ServerBundle\Exception");
        $this->repo->byAuthorAndName('author', 'name');
    }
    
    /**
     * Creates a mock package
     * 
     * @return \Packagist\Api\Result\Package
     */
    protected function createPackage()
    {
        $package = new \Packagist\Api\Result\Package();
        
        $version = new \Packagist\Api\Result\Package\Version();
        $version->fromArray(array('version' => '1.0.0'));
        
        $package->fromArray(
            array(
                'versions' => array($version),
                'description' => 'description',
                'repository' => 'https://a.repo'
            )
        );
        return $package;
    }
}