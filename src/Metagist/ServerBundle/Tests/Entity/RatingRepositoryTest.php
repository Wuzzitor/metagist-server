<?php
namespace Metagist\ServerBundle\Tests\Entity;

use Metagist\ServerBundle\Tests\WebDoctrineTestCase;
use Metagist\ServerBundle\Entity\RatingRepository;
use Metagist\ServerBundle\Entity\Rating;
use Metagist\ServerBundle\Entity\Package;
use Metagist\ServerBundle\Entity\User;

/**
 * Tests the rating repo class.
 * 
 * @author Daniel Pozzi <bonndan76@googlemail.com>
 */
class RatingRepositoryTest extends WebDoctrineTestCase
{
    /**
     * system under test
     * @var RatingRepository
     */
    private $repo;
    
    /**
     * Test setup
     */
    public function setUp()
    {
        parent::setUp();
        $this->repo = $this->entityManager->getRepository('MetagistServerBundle:Rating');
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
                    'package_id' => 13,
                    'rating' => 1,
                    'title' => 'testtitle',
                    'comment' => 'testcomment',
                    'identifier' => 'val123/xyz'))
            );
        $statement->expects($this->at(1))
            ->method('fetch')
            ->will($this->returnValue(false));
        
        
        $package = new Package('test/test123', 123);
        $collection = $this->repo->byPackage($package);
        $this->assertInstanceOf("\Doctrine\Common\Collections\Collection", $collection);
        $info = $collection->get(0);
        $this->assertInstanceOf("\Metagist\Rating", $info);
        $this->assertEquals('testcomment', $info->getComment());
        $this->assertInstanceOf("\Metagist\Package", $info->getPackage());
    }
    
    /**
     * Ensures a package rating for a given user is retrieved.
     */
    public function testByPackageAndUser()
    {
        $statement = $this->createMockStatement();
        $statement->expects($this->once())
            ->method('fetch')
            ->will($this->returnValue(
                array(
                    'package_id' => 13,
                    'rating' => 1,
                    'title' => 'testtitle',
                    'comment' => 'testcomment',
                    'identifier' => 'val123/xyz'))
            );
        
        $package = new Package('test/test123', 123);
        $user    = new User('test');
        $user->setId(22);
        
        $rating = $this->repo->byPackageAndUser($package, $user);
        $this->assertInstanceOf("\Metagist\ServerBundle\Entity\Rating", $rating);
        $this->assertEquals($user, $rating->getUser());
        $this->assertEquals($package, $rating->getPackage());
    }
    
    /**
     * Ensures the latest ratings can be retrieved.
     */
    public function testLatest()
    {
        $statement = $this->createMockStatement();
        $statement->expects($this->at(0))
            ->method('fetch')
            ->will($this->returnValue(
                array(
                    'package_id' => 13,
                    'rating' => 1,
                    'title' => 'testtitle',
                    'comment' => 'testcomment',
                    'identifier' => 'val123/xyz'))
            );
        $statement->expects($this->at(1))
            ->method('fetch')
            ->will($this->returnValue(false));
        
        $collection = $this->repo->latest();
        $this->assertInstanceOf("\Doctrine\Common\Collections\Collection", $collection);
        $info = $collection->get(0);
        $this->assertInstanceOf("\Metagist\ServerBundle\Entity\Rating", $info);
        $this->assertEquals('testcomment', $info->getComment());
        $this->assertInstanceOf("\Metagist\ServerBundle\Entity\Package", $info->getPackage());
    }
    
    /**
     * Ensures a package can be saved.
     */
    public function testSave()
    {
        $statement = $this->createMockStatement();
        
        $package = new Package('test/test123', 123);
        $rating = Rating::fromArray(array(
            'package' => $package,
            'user_id' => 13
        ));
        $this->repo->save($rating);
    }
    
    /**
     * Ensures a package cannot be saved without user_id.
     */
    public function testSaveNoUserIdException()
    {
        $package = new Package('test/test123', 123);
        $rating = Rating::fromArray(array(
            'package' => $package,
        ));
        $this->setExpectedException("\RuntimeException");
        $this->repo->save($rating);
    }
    
    /**
     * Ensures a package cannot be saved without a package.
     */
    public function testSaveNoPackageException()
    {
        $rating = Rating::fromArray(array(
            'user_id' => 13,
        ));
        $this->setExpectedException("\RuntimeException");
        $this->repo->save($rating);
    }
    
    /**
     * Ensures a package cannot be saved without a package.
     */
    public function testSaveNoPackageIdException()
    {
        $package = new Package('test/test123');
        $rating = Rating::fromArray(array(
            'user_id' => 13,
            'package' => $package,
        ));
        $this->setExpectedException("\RuntimeException");
        $this->repo->save($rating);
    }
    
    /**
     * Ensures a package is returned if found.
     */
    public function testBest()
    {
        $package = new Package('test/test123');
        $this->entityManager->persist($package);
        $rating = Rating::fromArray(array(
            'user_id' => 13,
            'package' => $package,
        ));
        $this->entityManager->persist($rating);
        
        $collection = $this->repo->best();
        $this->assertInstanceOf("\Doctrine\Common\Collections\ArrayCollection", $collection);
        $this->assertEquals(1, count($collection));
        $rating = $collection->get(0);
        $this->assertInstanceOf("\Metagist\Rating", $rating);
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