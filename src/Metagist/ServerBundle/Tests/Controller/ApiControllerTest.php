<?php
namespace Metagist\ServerBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Tests the api controller.
 * 
 * @author Daniel Pozzi <bonndan76@googlemail.com>
 */
class ApiControllerTest extends WebTestCase
{
    /**
     * Ensures the index action returns the routes.
     */
    public function testIndexReturnsRoutes()
    {
        $client = static::createClient();

        $client->request('GET', '/api');
        $json = $client->getResponse()->getContent();
        $routes = json_decode($json);
        $this->assertInternalType('array', $routes);
        $this->assertNotEmpty($routes);
    }
    
    /**
     * Ensures package info is returned as json.
     */
    public function testPackage()
    {
        $this->markTestSkipped();
        $api = $this->createMockApi();
        $serializer = \JMS\Serializer\SerializerBuilder::create()->build();
        $api->expects($this->any())
            ->method('getSerializer')
            ->will($this->returnValue($serializer));
        
        $packageRepo = $this->createPackageRepo('aname', 'apackage');
        $this->application->expects($this->once())
            ->method('packages')
            ->will($this->returnValue($packageRepo));
        $metainfoRepo = $this->createMetaInfoRepo();
        $this->application->expects($this->once())
            ->method('metainfo')
            ->will($this->returnValue($metainfoRepo));
        
        $response = $this->controller->package('aname', 'apackage');
        $this->assertInstanceOf('\Symfony\Component\HttpFoundation\Response', $response);
        $package = json_decode($response->getContent());
        $this->assertEquals('aname/apackage', $package->identifier);
    }
    
    /**
     * Tests the successful execution of pushInfo().
     */
    public function testPushInfo()
    {$this->markTestSkipped();
        $serviceProvider = new Api\ServiceProvider();
        
        $api = $this->createMockApi();
        $api->expects($this->once())
            ->method('validateRequest')
            ->will($this->returnValue('aconsumer'));
        $api->expects($this->any())
            ->method('getSerializer')
            ->will($this->returnValue($serviceProvider->getSerializer()));
        
        $api->expects($this->once())
            ->method('getSchemaValidator')
            ->will($this->returnValue($serviceProvider->getSchemaValidator()));
        $api->expects($this->once())
            ->method('getIncomingRequest')
            ->will($this->returnValue($this->createPushInfoRequest()));
        
        $this->createOpauthListenerMock();
        
        //package is found
        $packageRepo = $this->createPackageRepo('aname', 'apackage');
        $this->application->expects($this->any())
            ->method('packages')
            ->will($this->returnValue($packageRepo));
        
        //decode payload
        $metaInfoRepo = $this->createMetaInfoRepo();
        $metaInfoRepo->expects($this->once())
            ->method('save')
            ->with($this->isInstanceOf("\Metagist\MetaInfo"))
            ->will($this->returnCallback(array($this, 'validateMetaInfo')));
        $this->application->expects($this->once())
            ->method('metainfo')
            ->will($this->returnValue($metaInfoRepo));
        
        $response = $this->controller->pushInfo('aname', 'apackage');
        $this->assertInstanceOf('\Symfony\Component\HttpFoundation\Response', $response);
        $this->assertEquals(200, $response->getStatusCode());
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
     * @return \Symfony\Component\HttpFoundation\Request
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
        $serviceProvider = new \Metagist\Api\ServiceProvider();
        return $serviceProvider->getIncomingRequest($message);
    }
}