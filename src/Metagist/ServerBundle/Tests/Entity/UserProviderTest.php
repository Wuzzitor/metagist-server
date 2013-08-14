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
        $this->assertEquals(13, $user->getId());
    }
    
    /**
     * Ensures a new user is created when someone logs in using oauth.
     */
    public function testCreateUserFromOauthResponse()
    {
        $this->markTestIncomplete();
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