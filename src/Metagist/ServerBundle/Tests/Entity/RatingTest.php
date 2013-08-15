<?php
namespace Metagist\ServerBundle\Tests\Entity;

use Metagist\ServerBundle\Entity\Rating;
use Metagist\ServerBundle\Entity\User;
use Metagist\ServerBundle\Entity\Package;

/**
 * Tests the rating model
 * 
 * @author Daniel Pozzi <bonndan76@googlemail.com>
 */
class RatingTest extends \PHPUnit_Framework_TestCase
{
    /**
     * system under test
     * @var Rating 
     */
    private $rating;
    
    /**
     * Test setup
     */
    public function setUp()
    {
        parent::setUp();
        $this->rating = new Rating();
    }
    
    /**
     * Ensures the array factory method returns a rating object.
     */
    public function testFactoryMethod()
    {
        $rating = Rating::fromArray(array());
        $this->assertInstanceOf('Metagist\ServerBundle\Entity\Rating', $rating);
    }
    
    /**
     * Ensures the array factory method returns a rating object.
     */
    public function testSetUser()
    {
        $rating = Rating::fromArray(array());
        $user = new User('test', 'ROLE_ADMIN');
        $rating->setUser($user);
        $this->assertSame($user, $rating->getUser());
    }
    
    /**
     * Ensures the array factory method returns a rating object.
     */
    public function testGetUserIdFromUserObject()
    {
        $rating = Rating::fromArray(array());
        $user = new User('test', 'ROLE_ADMIN');
        $user->setId(13);
        $rating->setUser($user);
        $this->assertEquals($user, $rating->getUser());
    }
    
    /**
     * Ensures the value factory method returns a metainfo object.
     */
    public function testFactoryMethodStoresValues()
    {
        $package = new Package('aaa/bbb', 12);
        $user = new User('test');
        $rating = Rating::fromArray(
            array(
                'package' => $package,
                'user' => $user,
                'rating' => 3,
                'title' => 'test title',
                'comment' => 'test comment',
                'version' => '1.0.0',
                'time_updated' => '2012-12-12 00:00:00'
            )
        );
        $this->assertEquals($package, $rating->getPackage());
        $this->assertEquals(3, $rating->getRating());
        $this->assertEquals($user, $rating->getUser());
        $this->assertEquals('test title', $rating->getTitle());
        $this->assertEquals('test comment', $rating->getComment());
        $this->assertEquals('1.0.0', $rating->getVersion());
    }
    
}