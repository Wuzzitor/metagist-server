<?php
namespace Metagist\ServerBundle\Tests\Entity;

use Metagist\ServerBundle\Entity\User;

/**
 * Tests the user class.
 * 
 * @author Daniel Pozzi <bonndan76@googlemail.com>
 */
class UserTest extends \PHPUnit_Framework_TestCase
{
    /**
     * system under test
     * @var User
     */
    private $user;
    
    /**
     * Test setup.
     */
    public function setUp()
    {
        parent::setUp();
        $this->user = new User('test123', User::ROLE_USER, 'http://an.url');
    }
    
    /**
     * Ensures the provider implements the UserProviderInterface
     */
    public function testGetUserName()
    {
        $this->assertEquals('test123', $this->user->getUsername());
    }
    
    /**
     * Tests the id getter and setter
     */
    public function testId()
    {
        $this->user->setId(12);
        $this->assertEquals(12, $this->user->getId());
    }
    
    public function testGetAvatarUrl()
    {
        $this->assertEquals('http://an.url', $this->user->getAvatarUrl());
    }
    
    /**
     * Ensures to magic toString method returns the username.
     */
    public function testToStringReturnsUsername()
    {
        $this->assertEquals('test123', (string) $this->user);
    }
}