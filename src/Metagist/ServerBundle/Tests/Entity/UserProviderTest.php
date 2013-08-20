<?php
namespace Metagist\ServerBundle\Tests\Entity;

use Metagist\ServerBundle\Tests\WebDoctrineTestCase;
use Metagist\ServerBundle\Entity\User;
use Metagist\ServerBundle\Entity\UserProvider;

/**
 * Tests the user provider.
 * 
 * @author Daniel Pozzi <bonndan76@googlemail.com>
 */
class UserProviderTest extends WebDoctrineTestCase
{
    /**
     * system under test
     * @var UserProvider 
     */
    private $provider;
    
    /**
     * Test setup.
     */
    public function setUp()
    {
        parent::setUp();
        $this->provider = new UserProvider(
            $this->entityManager->getRepository('MetagistServerBundle:User'), 
            array('admins' => 'test123')
        );
    }
    
    /**
     * Ensures the provider implements the UserProviderInterface
     */
    public function testImplementsInterface()
    {
        $this->assertInstanceOf("Symfony\Component\Security\Core\User\UserProviderInterface", $this->provider);
    }
    
    /**
     * Ensures the connection is used to query the database.
     */
    public function testReturnsUser()
    {
        $this->entityManager->persist(new User('test'));
        $this->entityManager->flush();
        
        $user = $this->provider->loadUserByUsername('test');
        $this->assertInstanceOf('Metagist\ServerBundle\Entity\User', $user);
        $this->assertEquals(1, $user->getId());
    }
    
    /**
     * Ensures a new user is created when someone logs in using oauth.
     */
    public function testCreateUserFromOauthResponse()
    {
        $owner = $this->getMock("\HWI\Bundle\OAuthBundle\OAuth\ResourceOwnerInterface");
        $owner->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('bar'));
     
        $response = new \HWI\Bundle\OAuthBundle\Tests\Fixtures\CustomUserResponse();
        $response->setResourceOwner($owner);
        $user = $this->provider->loadUserByOAuthUserResponse($response);
        
        $this->assertInstanceOf('Metagist\ServerBundle\Entity\User', $user);
        $this->assertEquals(1, $user->getId());
        $this->assertEquals("foo@bar", $user->getUsername());
    }
    
    /**
     * Ensures a new user is created when someone logs in using oauth.
     */
    public function testLoadFromOauthResponse()
    {
        $user = new User('foo666@bar');
        $this->entityManager->persist($user);
        $this->entityManager->flush();
        
        $owner = $this->getMock("\HWI\Bundle\OAuthBundle\OAuth\ResourceOwnerInterface");
        $owner->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('bar'));
     
        $response = new \HWI\Bundle\OAuthBundle\Tests\Fixtures\CustomUserResponse();
        $response->setResourceOwner($owner);
        $user = $this->provider->loadUserByOAuthUserResponse($response);
        
        $this->assertInstanceOf('Metagist\ServerBundle\Entity\User', $user);
        $this->assertEquals(2, $user->getId());
    }
    
    /**
     * Ensures the admin configuration is regarded.
     */
    public function testLoadAdmin()
    {
        $this->entityManager->persist(new User('test123'));
        $this->entityManager->flush();
        
        $user = $this->provider->loadUserByUsername('test123');
        $this->assertContains(User::ROLE_ADMIN, $user->getRoles());
    }
}