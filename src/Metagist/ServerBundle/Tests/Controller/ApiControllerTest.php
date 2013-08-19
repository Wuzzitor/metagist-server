<?php
namespace Metagist\ServerBundle\Tests\Controller;

use Metagist\ServerBundle\Tests\WebDoctrineTestCase;
use Metagist\ServerBundle\Entity\Package;

/**
 * Tests the api controller.
 * 
 * @author Daniel Pozzi <bonndan76@googlemail.com>
 */
class ApiControllerTest extends WebDoctrineTestCase
{
    public function setUp()
    {
        parent::setUp();
        static::$client->followRedirects();
    }
    
    /**
     * Loads the databse fixture
     */
    protected function loadFixtures()
    {
        $package = new Package('test/test123');
        $package->setDescription('test');
        $package->setType('library');
        
        $this->entityManager->persist($package);
        $this->entityManager->flush();
    }
    
    /**
     * Ensures the index action returns the routes.
     */
    public function testIndexReturnsRoutes()
    {
        $routes = (array)$this->requestJson('/api');
        $this->assertArrayHasKey('api-homepage', $routes);
    }
    
    /**
     * Ensures package info is returned as json.
     */
    public function testPackageNotFoundByFactory()
    {
        $this->setExpectedException("\Exception", "at packagist");
        $this->requestJson('/api/package/bonndan/metagist-api');
    }
    
    /**
     * Ensures package info is returned as json.
     */
    public function testPackage()
    {
        $data = $this->requestJson('/api/package/test/test123');
        $this->assertEquals('test/test123', $data->identifier,  var_export($data, true));
    }
    
    /**
     * Tests the successful execution of pushInfo().
     * @link https://coderwall.com/p/hwb6qq
     */
    public function testPushInfo()
    {
        $this->markTestSkipped();
        $pushRequest = $this->createPushInfoRequest();
        static::$client->request(
            'POST', 
            "/api/pushInfo/test/test123", 
            array(),
            array(), 
            $pushRequest->getHeaders()->toArray(),
            $pushRequest->getBody() 
        );
        $response = static::$client->getResponse();
        $this->assertEquals(200, $response->getStatusCode(), $response->getContent());
    }
    
    /**
     * Callback method to validate the metaInfo received from testPushInfo()
     * 
     * '{"info":{"group":"repository","version":"dev-master","value":"https:\/\/github.com\/Matthimatiker\/MolComponents.git"}}'
     * 
     * @param \Metagist\MetaInfo $info
     */
    public function validateMetaInfo(MetaInfo $info)
    {
       $this->assertEquals('repository', $info->getGroup());
       $this->assertEquals('dev-master', $info->getVersion());
       $this->assertEquals('https://github.com/Matthimatiker/MolComponents.git', $info->getValue());
    }
    
    /**
     * Ensures the request is denied with proper authorization
     */
    public function testPushInfoFailsForWrongAuthorization()
    {$this->markTestSkipped();
        $api = $this->createMockApi();
        $api->expects($this->once())
            ->method('validateRequest')
            ->will($this->throwException(new \Metagist\Api\Exception('test')));
        $api->expects($this->once())
            ->method('getIncomingRequest')
            ->will($this->returnValue($this->createPushInfoRequest()));
        
        $repo = $this->createMetaInfoRepo();
        $repo->expects($this->never())
            ->method('save');
        $this->createOpauthListenerMock();
        
        
        $response = $this->controller->pushInfo('author', 'name');
        $this->assertInstanceOf('\Symfony\Component\HttpFoundation\Response', $response);
        $this->assertEquals(403, $response->getStatusCode());
    }
    
    /**
     * Ensures a 404 is sent if the package cannot be found.
     */
    public function testPushInfoFailsForinvalidJson()
    {$this->markTestSkipped();
        $api = $this->createMockApi();
        $api->expects($this->once())
            ->method('validateRequest')
            ->will($this->returnValue('aconsumer'));
        $serializerMock = $this->getMock("\JMS\Serializer\SerializerInterface");
        $api->expects($this->any())
            ->method('getSerializer')
            ->will($this->returnValue($serializerMock));
        $this->createOpauthListenerMock();
        
        $validatorMock = $this->getMockBuilder("\Metagist\Api\Validation\Plugin\SchemaValidator")
            ->disableOriginalConstructor()
            ->getMock();
        $validatorMock->expects($this->once())
            ->method('validateRequest')
            ->will($this->throwException(new \Metagist\Api\Validation\Exception('test', 400)));
        $api->expects($this->once())
            ->method('getSchemaValidator')
            ->will($this->returnValue($validatorMock));
        $api->expects($this->once())
            ->method('getIncomingRequest')
            ->will($this->returnValue($this->createPushInfoRequest()));
        
        $response = $this->controller->pushInfo('aname', 'apackage');
        $this->assertInstanceOf('\Symfony\Component\HttpFoundation\Response', $response);
        $this->assertEquals(400, $response->getStatusCode());
    }
    
    /**
     * Ensures a 404 is sent if the package cannot be found.
     */
    public function testPushInfoFailsForMissingPackage()
    {
        $this->markTestSkipped();
        $api = $this->createMockApi();
        $api->expects($this->once())
            ->method('validateRequest')
            ->will($this->returnValue('aconsumer'));
        $serializerMock = $this->getMock("\JMS\Serializer\SerializerInterface");
        $api->expects($this->any())
            ->method('getSerializer')
            ->will($this->returnValue($serializerMock));
        $this->createOpauthListenerMock();
        
        $validatorMock = $this->getMockBuilder("\Metagist\Api\Validation\Plugin\SchemaValidator")
            ->disableOriginalConstructor()
            ->getMock();
        $api->expects($this->once())
            ->method('getSchemaValidator')
            ->will($this->returnValue($validatorMock));
        $api->expects($this->once())
            ->method('getIncomingRequest')
            ->will($this->returnValue($this->createPushInfoRequest()));
        
        //package is found
        $packageRepo = $this->createPackageRepo('aname', 'apackage', true);
        $this->application->expects($this->once())
            ->method('packages')
            ->will($this->returnValue($packageRepo));
        
        $response = $this->controller->pushInfo('aname', 'apackage');
        $this->assertInstanceOf('\Symfony\Component\HttpFoundation\Response', $response);
        $this->assertEquals(404, $response->getStatusCode());
    }
    
    /**
     * 
     */
    protected function createOpauthListenerMock()
    {
        $listenerMock = $this->getMockBuilder("\Metagist\OpauthListener")
            ->disableOriginalConstructor()
            ->getMock();
        $listenerMock->expects($this->any())
            ->method('onWorkerAuthentication')
            ->with('aconsumer');
        $this->application->expects($this->any())
            ->method('getOpauthListener')
            ->will($this->returnValue($listenerMock));
    }
    /**
     * Creates a mocked api.
     * 
     * @return \Metagist\Api\ApiProviderInterface mock
     */
    protected function createMockApi()
    {
        $apiMock = $this->getMock("\Metagist\Api\ApiProviderInterface");
        $this->application->expects($this->any())
            ->method('getApi')
            ->will($this->returnValue($apiMock));
        
        return $apiMock;
    }
    
    
    /**
     * Mocks the json() behaviour of the application.
     * 
     * @param type $data
     * @param type $status
     * @param type $headers
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function json($data = array(), $status = 200, $headers = array())
    {
        return new \Symfony\Component\HttpFoundation\JsonResponse($data, $status, $headers);
    }
    
    /**
     * Creates a package repo mock.
     * 
     */
    protected function createPackageRepo($author, $name, $returnNull = false)
    {
        $packageRepo = $this->getMockBuilder("\Metagist\PackageRepository")
            ->disableOriginalConstructor()
            ->getMock();
        if (!$returnNull) {
            $package = new Package($author . "/" . $name);
        } else {
            $package = null;
        }
        
        $packageRepo->expects($this->once())
            ->method('byAuthorAndName')
            ->with($author, $name)
            ->will($this->returnValue($package));
        
        return $packageRepo;
    }
    
    /**
     * Creates a metainfo repo mock.
     * 
     */
    protected function createMetaInfoRepo(array $data = array())
    {
        $collection = new \Doctrine\Common\Collections\ArrayCollection($data);
        $repo = $this->getMockBuilder("\Metagist\MetaInfoRepository")
            ->disableOriginalConstructor()
            ->getMock();
        $repo->expects($this->any())
            ->method('byPackage')
            ->will($this->returnValue($collection));
        
        return $repo;
    }
    
    /**
     * Constructs a post request with payload.
     * 
     * @return \Guzzle\Http\Message\EntityEnclosingRequest
     */
    private function createPushInfoRequest()
    {
        $request = \Symfony\Component\HttpFoundation\Request::create(
            'http://test.com',
            'POST',
            array('author' => 'test', 'name' => 'test'),
            array(),
            array(),
            array(),
            '{"info":{"group":"repository","version":"dev-master","value":"https:\/\/github.com\/Matthimatiker\/MolComponents.git"}}'
        );
        
        $message = $request->__toString();
        $serviceProvider = new \Metagist\Api\Factory();
        return $serviceProvider->getIncomingRequest($message);
    }
    
    /**
     * Launch a request against the api.
     * 
     * @param string $path
     * @param string $method
     * @return array
     */
    private function requestJson($path, $method = 'GET')
    {
        static::$client->request($method, $path);
        $json = static::$client->getResponse()->getContent();
        if (static::$client->getResponse()->getStatusCode() != 200) {
            throw new \Exception('Request failed: ' . $json);
        }
        return json_decode($json);
    }
}