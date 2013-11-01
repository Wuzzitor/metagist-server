<?php
namespace Metagist\WorkerBundle\Tests\Scanner;

use Metagist\WorkerBundle\Scanner\GitHub;
use Metagist\ServerBundle\Entity\Package;
use Metagist\ServerBundle\Entity\Metainfo;

/**
 * Tests the github scanner.
 * 
 * @author Daniel Pozzi <bonndan76@googlemail.com>
 */
class GitHubTest extends \PHPUnit_Framework_TestCase
{
    /**
     * system under test
     * @var GitHub
     */
    private $scanner;
    
    /**
     * Test setup.
     * 
     */
    public function setUp()
    {
        $this->scanner = new GitHub($this->getMock("Psr\Log\LoggerInterface"));
    }
    
    /**
     * Ensures that only github.com urls are parsed.
     */
    public function testFromGithubRepoReturnsEmptyIfUrlNotGithub()
    {
        $client = $this->getMockBuilder("\Github\Client")
           ->disableOriginalConstructor()
           ->getMock();
        $this->scanner->setGitHubClient($client);
        $result = $this->scanner->scan($this->createPackage('http://an.url'));
        $this->assertInternalType('array', $result);
        $this->assertEmpty($result);
    }
    
    /**
     * Ensures that only urls with path are parsed.
     */
    public function testFromGithubRepoReturnsEmptyIfNoPath()
    {
        $client = $this->createClientReturning('');
        $this->scanner->setGitHubClient($client);
        
        $result = $this->scanner->scan($this->createPackage('https://github.com/'));
        $this->assertInternalType('array', $result);
        $this->assertEmpty($result);
    }
    
    /**
     * Ensures that only urls with path are parsed.
     */
    public function testFromGithubRepoCollectsContributorsAndCommits()
    {
        $client = $this->createClientReturning(array());
        $this->scanner->setGitHubClient($client);
        $result = $this->scanner->fromGithubRepo("owner", "repo");
        $this->assertInternalType('array', $result);
    }
    
    /**
     * Ensures the scraper works.
     */
    public function testFromGithubPage()
    {
        $client = $this->createClientReturning('<div class="social-count">123</div>');
        $this->scanner->setGitHubClient($client);
        
        $collection = $this->scanner->fromGithubPage('bonndan', 'MdMan');
        $this->assertInternalType('array', $collection);
        $metaInfo = current($collection);
        $this->assertEquals(123, $metaInfo->getValue());
    }
    
    /**
     * Ensures the scraper works.
     */
    public function testFromGithubIssuePage()
    {
        $needle = '<div class="issues-list-options" data-pjax="">
    <div class="button-group">
        <a href="/Wuzzitor/metagist.org/issues?page=1&amp;state=open" class="minibutton selected">
          4 Open
        </a>
        <a href="/Wuzzitor/metagist.org/issues?page=1&amp;state=closed" class="minibutton ">
          5 Closed
        </a>
    </div></div>';
        $client = $this->createClientReturning($needle);
        $this->scanner->setGitHubClient($client);
        
        $collection = $this->scanner->fromGithubIssuePage('bonndan', 'MdMan');
        $this->assertInternalType('array', $collection);
        
        $metaInfo = $collection[0];
        $this->assertInstanceOf("\Metagist\ServerBundle\Entity\MetaInfo", $metaInfo);
        $this->assertEquals(4, $metaInfo->getValue());
        
        $metaInfo = $collection[1];
        $this->assertInstanceOf("\Metagist\ServerBundle\Entity\MetaInfo", $metaInfo);
        $this->assertEquals(5, $metaInfo->getValue());
    }
    
    /**
     * Creates a mock of \Github\Client
     * 
     * @param mixed $returnedResponse
     * @return \Github\Client
     */
    protected function createClientReturning($returnedResponse)
    {
        $client = $this->getMockBuilder("\Github\Client")
           ->disableOriginalConstructor()
           ->getMock();
        $response = $this->getMock("\Github\HttpClient\Message\Response");
        $response->expects($this->any())
            ->method('getContent')
            ->will($this->returnValue($returnedResponse));
        $httpClient = $this->getMock("\Github\HttpClient\HttpClientInterface");
        $httpClient->expects($this->any())
            ->method('get')
            ->will($this->returnValue($response));
        $client->expects($this->any())
            ->method('getHttpClient')
            ->will($this->returnValue($httpClient));
        
        return $client;
    }
    
    /**
     * Creates a package with a repo url.
     * 
     * @param string $repoUrl
     * @return \Metagist\Package
     */
    protected function createPackage($repoUrl)
    {
        $package = new Package('test/test');
        
        $collection = new \Doctrine\Common\Collections\ArrayCollection(
            array(MetaInfo::fromValue(MetaInfo::REPOSITORY, $repoUrl))
        );
        $package->setMetaInfos($collection);
        
        return $package;
    }
}
