<?php
namespace Metagist\ServerBundle\Tests\Entity;

use Metagist\ServerBundle\Entity\MetainfoRepositoryProxy;
use Metagist\ServerBundle\Entity\Metainfo;
use Metagist\ServerBundle\Entity\User;
use Metagist\ServerBundle\Entity\Package;
use Metagist\CategorySchema;

/**
 * Tests the metainfo repo class.
 * 
 * @author Daniel Pozzi <bonndan76@googlemail.com>
 */
class MetaInfoRepositoryProxyTest extends \PHPUnit_Framework_TestCase
{
    /**
     * system under test
     * @var MetaInfoRepositoryProxy
     */
    private $proxy;
    
    /**
     * repo mock
     * @var MetaInfoRepository
     */
    private $repo;
    
    /**
     * security context mock
     * @var \Symfony\Component\Security\Core\SecurityContextInterface
     */
    private $context;
    
    /**
     * category schema
     * @var \Metagist\CategorySchema
     */
    private $schema;
    
    /**
     * Test setup
     */
    public function setUp()
    {
        parent::setUp();
        $this->repo = $this->getMockBuilder("\Metagist\ServerBundle\Entity\MetaInfoRepository")
            ->disableOriginalConstructor()
            ->getMock();
        $this->context = $this->getMockBuilder("\Symfony\Component\Security\Core\SecurityContextInterface")
            ->disableOriginalConstructor()
            ->getMock();
        $json = file_get_contents(__DIR__ .'/testdata/testcategories.json');
        $this->schema = new CategorySchema($json);
        $this->proxy  = new MetaInfoRepositoryProxy($this->repo, $this->context, $this->schema);
    }
    
    
    /**
     * Ensures a package can be saved.
     */
    public function testSave()
    {
        $metaInfo = Metainfo::fromValue('testInteger', true);
            
        $this->context->expects($this->once())
            ->method('isGranted')
            ->with('ROLE_USER')
            ->will($this->returnValue(true));
        $this->repo->expects($this->once())
            ->method('save')
            ->with($metaInfo);
        
        $this->proxy->save($metaInfo);
    }
    
    /**
     * Ensures a package can be saved.
     */
    public function testSaveIsForbidden()
    {
        $this->context->expects($this->once())
            ->method('isGranted')
            ->with('ROLE_USER')
            ->will($this->returnValue(false));
        $user = new User('test', null, User::ROLE_USER);
        $token = new \Symfony\Component\Security\Core\Authentication\Token\PreAuthenticatedToken($user, NULL, 'api');
        $this->context->expects($this->once())
            ->method('getToken')
            ->will($this->returnValue($token));
        $this->repo->expects($this->never())
            ->method('save');
        
        $this->setExpectedException("\Symfony\Component\Security\Core\Exception\AccessDeniedException");
        $this->proxy->save(Metainfo::fromValue('testInteger', true));
    }
    
    /**
     * Ensures the call interceptor forwards method calls.
     */
    public function testForwarding()
    {
        $package = new Package('test');
        $this->repo->expects($this->once())
            ->method('savePackage')
            ->with($package);
        $this->proxy->savePackage($package);
    }
}