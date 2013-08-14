<?php
namespace Metagist\ServerBundle\Tests\Entity;

use Metagist\ServerBundle\Tests\WebDoctrineTestCase;
use Metagist\ServerBundle\Entity\MetainfoRepository;
use Metagist\ServerBundle\Resources\CategorySchema;
use Metagist\ServerBundle\Entity\Package;
use Metagist\ServerBundle\Entity\Metainfo;

/**
 * 
 * Tests the metainfo repo class.
 * 
 * @author Daniel Pozzi <bonndan76@googlemail.com>
 */
class MetaInfoRepositoryTest extends WebDoctrineTestCase
{
    /**
     * system under test
     * @var MetaInfoRepository
     */
    private $repo;
    
    /**
     * Test setup
     */
    public function setUp()
    {
        parent::setUp();
        $kernel = self::createKernel();
        $kernel->boot();
        $doctrine = $kernel->getContainer()->get('doctrine');
        $this->repo = $doctrine->getManager()->getRepository('MetagistServerBundle:Metainfo');
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
        
        $package = new Package('test/test123', 123);
        $collection = $this->repo->byPackage($package);
        $this->assertInstanceOf("\Doctrine\Common\Collections\Collection", $collection);
        $info = $collection->get(0);
        $this->assertInstanceOf("\Metagist\ServerBundle\Entity\Metainfo", $info);
    }
    
    /**
     * Ensures a package can be saved.
     */
    public function testSavePackage()
    {
        $elements = array(Metainfo::fromValue('test/test', 123));
        $collection = new \Doctrine\Common\Collections\ArrayCollection($elements);
        $package = new Package('test/test123', 123);
        $package->setMetaInfos($collection);
        
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
        
        $collection = $this->repo->latest();
        $this->assertInstanceOf("\Doctrine\Common\Collections\ArrayCollection", $collection);
        $this->assertEquals(1, count($collection));
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