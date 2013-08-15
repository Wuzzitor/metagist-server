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
    
    private $package;
    private $user;
    private $rating;
        
    /**
     * Test setup
     */
    public function setUp()
    {
        parent::setUp();
        $this->repo = $this->entityManager->getRepository('MetagistServerBundle:Rating');
    }
    
    public function loadFixtures()
    {
        $faker = \Faker\Factory::create();
        
        $this->package = new Package('test/' . $faker->domainWord);
        $this->package->setDescription('test');
        $this->entityManager->persist($this->package);
        $this->entityManager->flush();
        
        $this->user = new User($faker->username);
        $this->entityManager->persist($this->user);
        $this->entityManager->flush();
        
        $this->rating = new Rating();
        $this->rating->setPackage($this->package);
        $this->rating->setUser($this->user);
        $this->rating->setComment('testcomment');
        $this->rating->setRating($faker->randomNumber(1, 5));
        $this->rating->setTitle('Superb');
        
        $this->entityManager->persist($this->rating);
        $this->entityManager->flush();
    }
    
    /**
     * Ensures the params are validated.
     */
    public function testByPackage()
    {
        $collection = $this->repo->byPackage($this->package);
        $this->assertInstanceOf("\Doctrine\Common\Collections\Collection", $collection);
        $rating = $collection->get(0);
        $this->assertInstanceOf("\Metagist\ServerBundle\Entity\Rating", $rating);
        $this->assertEquals('testcomment', $rating->getComment());
        $this->assertSame($this->package, $rating->getPackage());
    }
    
    /**
     * Ensures a package rating for a given user is retrieved.
     */
    public function testByPackageAndUser()
    {
        $rating = $this->repo->byPackageAndUser($this->package, $this->user);
        $this->assertInstanceOf("\Metagist\ServerBundle\Entity\Rating", $rating);
        $this->assertEquals($this->user, $rating->getUser());
        $this->assertEquals($this->package, $rating->getPackage());
    }
    
    /**
     * Ensures the latest ratings can be retrieved.
     */
    public function testLatest()
    {
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
        $rating = Rating::fromArray(array(
            'package' => $this->package,
            'user' => $this->user,
            'title' => 'test',
            'comment' => 'test',
            'rating' => 4,
        ));
        
        $this->repo->save($rating);
    }
    
    /**
     * Ensures a package is returned if found.
     */
    public function testBest()
    {
        $this->loadFixtures();
        
        $collection = $this->repo->best();
        $this->assertInstanceOf("\Doctrine\Common\Collections\ArrayCollection", $collection);
        $this->assertEquals(2, count($collection));
        $rating = $collection->first();
        $this->assertInstanceOf("\Metagist\ServerBundle\Entity\Package", $rating);
    }
    
}