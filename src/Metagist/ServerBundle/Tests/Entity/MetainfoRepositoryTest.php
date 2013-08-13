<?php
namespace Metagist\ServerBundle\Tests\Entity;

use Metagist\ServerBundle\Entity\MetainfoRepository;

/**
 * 
 * Tests the metainfo repo class.
 * 
 * @author Daniel Pozzi <bonndan76@googlemail.com>
 */
class MetaInfoRepositoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * system under test
     * @var MetaInfoRepository
     */
    private $repo;
    
    /**
     * connection mock
     * @var \Doctrine\DBAL\Connection 
     */
    private $connection;
    
    /**
     * validator mock
     * @var Validator
     */
    private $validator;
    
    /**
     * Test setup
     */
    public function setUp()
    {
        parent::setUp();
        $this->connection = $this->getMockBuilder("\Doctrine\DBAL\Connection")
            ->disableOriginalConstructor()
            ->getMock();
        $this->validator = $this->getMockBuilder("\Metagist\Validator")
            ->disableOriginalConstructor()
            ->getMock();
        $this->repo = new MetainfoRepository($this->connection, $this->validator);
    }
    
    /**
     * Ensures the params are validated.
     */
    public function testByPackage()
    {
        $statement = $this->createMockStatement();
        $statement->expects($this->at(0))
            ->method('fetch')
            ->will($this->returnValue(
                array(
                    'group' => 'group123',
                    'value' => 'val123'))
            );
        $statement->expects($this->at(1))
            ->method('fetch')
            ->will($this->returnValue(false));
        
        $this->connection->expects($this->once())
            ->method('executeQuery')
            ->will($this->returnValue($statement));
        
        $package = new Package('test/test123', 123);
        $collection = $this->repo->byPackage($package);
        $this->assertInstanceOf("\Doctrine\Common\Collections\Collection", $collection);
        $info = $collection->get(0);
        $this->assertInstanceOf("\Metagist\MetaInfo", $info);
    }
    
    /**
     * Ensures a package can be saved.
     */
    public function testSavePackage()
    {
        $elements = array(MetaInfo::fromValue('test/test', 123));
        $collection = new \Doctrine\Common\Collections\ArrayCollection($elements);
        $package = new Package('test/test123', 123);
        $package->setMetaInfos($collection);
        
        $statement = $this->createMockStatement();
        $statement->expects($this->at(0))
            ->method('rowCount')
            ->will($this->returnValue(1));
        
        $this->connection->expects($this->at(0))
            ->method('executeQuery');
        $this->connection->expects($this->at(1))
            ->method('executeQuery')
            ->with($this->stringContains('INSERT INTO metainfo'))
            ->will($this->returnValue($statement));
        
        $this->repo->savePackage($package);
    }
    
        /**
     * Ensures a package is returned if found.
     */
    public function testGetLatest()
    {
        $data = array(
            'id' => 1,
            'identifier' => 'test/test',
            'description' => 'test',
            'versions' => 'dev-master',
            'package_id' => 1,
        );
        $statement = $this->createMockStatement();
        $statement->expects($this->at(0))
            ->method('fetch')
            ->will($this->returnValue($data));
        $statement->expects($this->at(1))
            ->method('fetch')
            ->will($this->returnValue(false));
        
        $this->connection->expects($this->once())
            ->method('executeQuery')
            ->will($this->returnValue($statement));
        
        $collection = $this->repo->latest();
        $this->assertInstanceOf("\Doctrine\Common\Collections\ArrayCollection", $collection);
        $this->assertEquals(1, count($collection));
    }
    
    /**
     * Ensures metainfos with cardinality 1 are replaced.
     */
    public function testSaveWithCardinalityOne()
    {
        $metaInfo = MetaInfo::fromValue('test/test', 123);
        $package = new Package('test/test123', 123);
        $metaInfo->setPackage($package);
        
        $statement = $this->createMockStatement();
        $statement->expects($this->at(0))
            ->method('rowCount')
            ->will($this->returnValue(1));
        
        $this->connection->expects($this->at(0))
            ->method('executeQuery')
            ->with("DELETE FROM metainfo WHERE package_id = ? AND `group` = ?");
        $this->connection->expects($this->at(1))
            ->method('executeQuery')
            ->with($this->stringContains('INSERT INTO metainfo'))
            ->will($this->returnValue($statement));
        
        $this->repo->save($metaInfo, 1);
    }
    
    /**
     * Creates a statement mock, the provided HydratorMockStatement seems to be broken.
     * 
     * @param array $methods
     * @return Statement mock
     */
    protected function createMockStatement(array $methods = array('rowCount', 'fetch'))
    {
        return $this->getMock('stdClass', $methods);
    }
}