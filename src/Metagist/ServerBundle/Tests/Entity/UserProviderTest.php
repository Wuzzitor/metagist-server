<?php
namespace Metagist\ServerBundle\Tests\Entity;

use Metagist\ServerBundle\Entity\UserProvider;

/**
 * Tests the user provider.
 * 
 * @author Daniel Pozzi <bonndan76@googlemail.com>
 */
class UserProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * system under test
     * @var UserProvider 
     */
    private $provider;
    
    /**
     * em
     * @var \Doctrine\ORM\EntityManager 
     */
    private $repo;
    
    /**
     * Test setup.
     */
    public function setUp()
    {
        parent::setUp();
        $this->repo = $this->getMockBuilder("\Doctrine\ORM\EntityRepository")
            ->disableOriginalConstructor()
            ->getMock();
        $this->provider = new UserProvider($this->repo, array());
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
        $statement = $this->createMockStatement();
        $statement->expects($this->once())
            ->method('fetch')
            ->will($this->returnValue(array('username' => 'test', 'avatar_url' => 'http://ava.tar', 'id' => 13)));
        
        $this->repo->expects($this->once())
            ->method('executeQuery')
            ->will($this->returnValue($statement));
        
        $user = $this->provider->loadUserByUsername('test');
        $this->assertInstanceOf('Metagist\User', $user);
        $this->assertEquals(13, $user->getId());
    }
    
    /**
     * Ensures a new user is created when someone logs in using oauth.
     */
    public function testCreateUserFromOauthResponse()
    {
        $response = array(
            'auth' => array(
                'raw' => array(
                    'login' => 'test123',
                    'avatar_url' => 'http://ava.tar'
                )
            )
        );
        
        $statement = $this->createMockStatement();
        $statement->expects($this->once())
            ->method('rowCount')
            ->will($this->returnValue(1));
        $statement->expects($this->once())
            ->method('fetch')
            ->will($this->returnValue(null));
        
        $this->repo->expects($this->at(0))
            ->method('executeQuery')
            ->will($this->returnValue($statement));
        $this->repo->expects($this->at(1))
            ->method('executeQuery')
            ->will($this->returnValue($statement));
        $this->repo->expects($this->once())
            ->method('lastInsertId')
            ->will($this->returnValue(13));
        
        $user = $this->provider->createUserFromOauthResponse($response);
        $this->assertInstanceOf('Metagist\User', $user);
        $this->assertEquals('test123', $user->getUsername());
        $this->assertEquals(13, $user->getId());
    }
    
    /**
     * Ensures the admin configuration is regarded.
     */
    public function testLoadAdmin()
    {
        $this->provider = new UserProvider($this->repo, array('admins' => 'test123'));
        $statement = $this->createMockStatement();
        $statement->expects($this->once())
            ->method('fetch')
            ->will($this->returnValue(array('username' => 'test123', 'avatar_url' => 'http://ava.tar', 'id' => 13)));
        
        $this->repo->expects($this->once())
            ->method('executeQuery')
            ->will($this->returnValue($statement));
        
        $user = $this->provider->loadUserByUsername('test123');
        $this->assertContains(User::ROLE_ADMIN, $user->getRoles());
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