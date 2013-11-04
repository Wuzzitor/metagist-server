<?php
namespace Metagist\ServerBundle\Tests\Entity;

use Metagist\ServerBundle\Entity\Package;
use Metagist\ServerBundle\Entity\Metainfo;
use Metagist\ServerBundle\Tests\WebDoctrineTestCase;

/**
 * Tests the package class.
 * 
 * @author Daniel Pozzi <bonndan76@googlemail.com>
 */
class PackageTest extends WebDoctrineTestCase
{
    /**
     * system under test
     * @var Package 
     */
    private $package;
    
    public function setUp()
    {
        parent::setUp();
        $this->package = new Package('test/t123');
    }
    
    /**
     * Ensures the constructor assigns the identifier.
     */
    public function testAssertConstructorWorks()
    {
        $this->assertEquals('test/t123', $this->package->getIdentifier());
    }
    
    /**
     * Ensures the author part can be extracted.
     */
    public function testGetAuthor()
    {
        $this->assertEquals('test', $this->package->getAuthor());
    }
    
    /**
     * Ensures the name part can be extracted.
     */
    public function testGetName()
    {
        $this->assertEquals('t123', $this->package->getName());
    }
    
    /**
     * Ensures the name part extraction fails with wrong identifier
     */
    public function testGetNameFails()
    {
        $this->package = new Package('test');
        $this->assertFalse($this->package->getName());
    }
    
    /**
     * Ensures description getter / setter are working
     */
    public function testDescription()
    {
        $this->package->setDescription('test');
        $this->assertEquals('test', $this->package->getDescription());
    }
    
    /**
     * Ensures versions getter / setter are working
     */
    public function testVersions()
    {
        $this->package->setVersions(array('test', '1.0.1'));
        $this->assertEquals(array('test', '1.0.1'), $this->package->getVersions());
    }
    
    /**
     * Ensures versions getter / setter are working
     */
    public function testGetTimeUpdated()
    {
        $time = new \DateTime();
        $this->package->setTimeUpdated($time);
        $this->assertEquals($time, $this->package->getTimeUpdated());
    }
    
    /**
     * Ensures a collection of metainfos can be passed.
     */
    public function testCollections()
    {
        $collection = new \Doctrine\Common\Collections\ArrayCollection(
            array(Metainfo::fromValue('test/test', 'test'))
        );
        $this->package->setMetaInfos($collection);
        $this->assertEquals($collection, $this->package->getMetaInfos());
    }
    
    /**
     * Ensures type getter / setter are working
     */
    public function testType()
    {
        $this->package->setType('library');
        $this->assertEquals('library', $this->package->getType());
    }
    
    /**
     * Ensures the collection is filtered by category
     */
    public function testGetMetaInfosWithGroupFilter()
    {
        $collection = new \Doctrine\Common\Collections\ArrayCollection(
            array(
                Metainfo::fromValue('test', 'test'),
                Metainfo::fromValue('test1', 'test'),
                Metainfo::fromValue('test', 'test'),
            )
        );
        $this->package->setMetaInfos($collection);
        
        $filtered = $this->package->getMetaInfos('test');
        $this->assertInstanceOf("\Doctrine\Common\Collections\ArrayCollection", $filtered);
        $this->assertEquals(2, count($filtered));
    }
    
    /**
     * Ensures the toString works
     */
    public function testToStringReturnsTheIdentifier()
    {
        $this->assertEquals($this->package->getIdentifier(), $this->package->__toString());
    }
    
    /**
     * Ensures the overall rating can be set.
     */
    public function testOveralRating()
    {
        $this->package->setDescription('');
        
        $this->package->setOverallRating(4.2);
        $this->entityManager->persist($this->package);
        $this->entityManager->flush();
        $this->assertEquals(4.2, $this->package->getOverallRating());
    }
    
    /**
     * Ensures the overall rating is checked if within bounds.
     */
    public function testOveralRatingOutOfBoundsWhenLowerZero()
    {
        $this->setExpectedException("\OutOfBoundsException");
        $this->package->setOverallRating(-0.1);
    }
    
    /**
     * Ensures the overall rating is checked if within bounds.
     */
    public function testOveralRatingOutOfBoundsWhenAbove5()
    {
        $this->setExpectedException("\OutOfBoundsException");
        $this->package->setOverallRating(5.1);
    }
}