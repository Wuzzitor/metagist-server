<?php
namespace Metagist\ServerBundle\Tests\Entity;

use Metagist\ServerBundle\Entity\DependencyFactory;

/**
 * Tests the dep factory
 * 
 * @author Daniel Pozzi <bonndan76@googlemail.com>
 */
class DependencyFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * system under test
     * @var DependencyFactory
     */
    private $factory;
    
    /**
     * Test setup
     */
    public function setUp()
    {
        parent::setUp();
        $this->factory = new DependencyFactory();
    }
    
    /**
     * Ensures a collection of metainfos is returned.
     */
    public function testFromPackagistPackage()
    {
        $package = $this->getMockBuilder("\Packagist\Api\Result\Package")
            ->disableOriginalConstructor()
            ->getMock();
        
        $version = $this->getMock("\Packagist\Api\Result\Package\Version");
        $version->expects($this->once())
            ->method('getRequire')
            ->will($this->returnValue(
                array(
                    'test/test' => '0.0.1',
                    'test/test123' => '1.2.3',
                    )
            ));
        $versions = array(
            $version
        );
        $package->expects($this->once())
            ->method('getVersions')
            ->will($this->returnValue($versions));
        
        $result = $this->factory->fromPackagistPackage($package);
        $this->assertInternalType('array', $result);
        $this->assertEquals(2, count($result));
        $this->assertInstanceOf("\Metagist\ServerBundle\Entity\Dependency", current($result));
    }
}