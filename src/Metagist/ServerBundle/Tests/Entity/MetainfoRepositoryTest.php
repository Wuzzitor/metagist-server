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
     * 
     * @var \Metagist\ServerBundle\Entity\MetainfoRepository
     */
    private $repo;
    
    /**
     * @var Package
     */
    private $package;
    
    /**
     * Test setup
     */
    public function setUp()
    {
        parent::setUp();
        $this->repo = $this->entityManager->getRepository('MetagistServerBundle:Metainfo');
    }
    
    protected function loadFixtures()
    {
        $faker = \Faker\Factory::create();
        
        $this->package = new Package('test/test123');
        $this->package->setDescription($faker->text);
        $this->entityManager->persist($this->package);

        $metaInfo = Metainfo::fromValue('test', $faker->domainName);
        $metaInfo->setPackage($this->package);
        $this->entityManager->persist($metaInfo);
        $this->entityManager->flush();
    }


    /**
     * Ensures the related metainfo is returned.
     */
    public function testByPackage()
    {
        $collection = $this->repo->byPackage($this->package);
        $this->assertInstanceOf("\Doctrine\Common\Collections\Collection", $collection);
        $info = $collection->get(0);
        $this->assertInstanceOf("\Metagist\ServerBundle\Entity\Metainfo", $info);
    }
    
    /**
     * Ensures that old infos are replaced.
     */
    public function testReplace()
    {
        $value = uniqid();
        $metaInfo = Metainfo::fromValue('test', $value);
        $metaInfo->setPackage($this->package);
        
        $this->repo->save($metaInfo, true);
        
        $remaining = $this->repo->findAll();
        $this->assertCount(1, $remaining);
        $info = current($remaining);
        $this->assertEquals($value, $info->getValue());
    }
    
        /**
     * Ensures a package is returned if found.
     */
    public function testGetLatest()
    {
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