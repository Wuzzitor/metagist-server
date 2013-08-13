<?php
namespace Metagist\ServerBundle\Tests\Entity;

use Metagist\ServerBundle\Entity\Metainfo;

/**
 * Tests the metainfo class.
 * 
 * @author Daniel Pozzi <bonndan76@googlemail.com>
 */
class MetaInfoTest extends \PHPUnit_Framework_TestCase
{
    /**
     * system under test
     * @var MetaInfo 
     */
    private $metaInfo;
    
    /**
     * Test setup
     */
    public function setUp()
    {
        parent::setUp();
        $this->metaInfo = new Metainfo();
    }
    
    /**
     * Ensures the array factory method returns a metainfo object.
     */
    public function testFactoryMethod()
    {
        $info = MetaInfo::fromArray(array());
        $this->assertInstanceOf('Metagist\MetaInfo', $info);
    }
    
    /**
     * Ensures the value factory method returns a metainfo object.
     */
    public function testFromValueFactoryMethod()
    {
        $info = MetaInfo::fromValue('grp', 'test123', '1.0.0');
        $this->assertInstanceOf('Metagist\MetaInfo', $info);
        $this->assertEquals('grp', $info->getGroup());
        $this->assertEquals('test123', $info->getValue());
        $this->assertEquals('1.0.0', $info->getVersion());
    }
    
    /**
     * Tests the group getter.
     */
    public function testGetGroup()
    {
        $this->metaInfo = MetaInfo::fromArray(array('group' => 'test'));
        $this->assertEquals('test', $this->metaInfo->getGroup());
    }
    
    /**
     * Tests the value getter.
     */
    public function testGetValue()
    {
        $this->metaInfo = MetaInfo::fromArray(array('value' => 'test'));
        $this->assertEquals('test', $this->metaInfo->getValue());
    }
    
    /**
     * Tests the version getter.
     */
    public function testGetVersion()
    {
        $this->metaInfo->setVersion('abc');
        $this->assertEquals('abc', $this->metaInfo->getVersion());
    }
    
    /**
     * Tests the time getter.
     */
    public function testGetTimeUpdated()
    {
        $this->metaInfo = MetaInfo::fromArray(array('time_updated' => '2012-12-12 00:00:00'));
        $this->assertEquals('2012-12-12 00:00:00', $this->metaInfo->getTimeUpdated());
    }
    
    /**
     * Tests the user id getter.
     */
    public function testGetUserId()
    {
        $this->metaInfo = MetaInfo::fromArray(array('user_id' => 13));
        $this->assertEquals(13, $this->metaInfo->getUserId());
    }
}