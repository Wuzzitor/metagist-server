<?php
namespace Metagist\WorkerBundle\Tests\Scanner\GithubApi;

use Metagist\WorkerBundle\Scanner\GithubApi\Stats;

/**
 * Tests the stats api client
 * 
 * @author Daniel Pozzi <bonndan76@googlemail.com>
 */
class StatsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * system under test
     * 
     * @var Metagist\WorkerBundle\Scanner\GithubApi\Stats
     */
    private $stats;
    
    /**
     * client mock
     * 
     * @var \Github\Client 
     */
    private $client;
    
    /**
     * Ensures the client is called with the proper request path.
     */
    public function testContributorsCallsGetOnClient()
    {
        $username   = 'testuser';
        $repository = 'testrepo';
        $request    = 'repos/'.urlencode($username).'/'.urlencode($repository).'/stats/contributors';
        $expected   = array('test');
        
        $this->createClientMockWithResponse($request, $expected);
        $this->stats = new Stats($this->client);
        $result = $this->stats->contributors($username, $repository);
        $this->assertEquals($expected, $result);
    }
    
    /**
     * Creates the client mock
     * 
     * @param mixed $responseContent
     */
    private function createClientMockWithResponse($path, $responseContent)
    {
        $response = $this->getMock("\Github\HttpClient\Message\Response");
        $response->expects($this->any())
            ->method('getContent')
            ->will($this->returnValue($responseContent));
        $httpClient = $this->getMock("\Github\HttpClient\HttpClientInterface");
        $httpClient->expects($this->any())
            ->method('get')
            ->with($path, array(), array())
            ->will($this->returnValue($response));
        
        $this->client = $this->getMockBuilder("Github\Client")
            ->disableOriginalConstructor()
            ->getMock();
        $this->client->expects($this->any())
            ->method('getHttpClient')
            ->will($this->returnValue($httpClient));
    }
}