<?php
namespace Metagist\WorkerBundle\Scanner;

use Metagist\ServerBundle\Entity\Metainfo;
use Metagist\ServerBundle\Entity\Package;
use Metagist\WorkerBundle\Scanner\GithubApi\Stats;

/**
 * Scanner to query and scrape info from github.com
 * 
 * @author Daniel Pozzi <bonndan76@googlemail.com>
 */
class GitHub extends Base implements ScannerInterface
{
    /**
     * fake user agent
     * 
     * @var string
     */
    const USER_AGENT = "Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; WOW64; Trident/5.0";
    
    /**
     * Configuration key where the github credentials are stored
     * 
     * @var string
     */
    const GITHUB_CLIENT_CONFIG = 'metagist.github.credentials';
    
    /**
     * github client
     * 
     * @var \Github\Client
     */
    private $client;
    
    /**
     * github url
     * 
     * @var string
     */
    private $githubBaseUrl = 'https://github.com';
    
    /**
     * Scans github pages.
     * 
     * @param Package $package
     * @return Metainfo[]
     */
    public function scan(Package $package)
    {
        $repos = $package->getMetaInfos(MetaInfo::REPOSITORY);
        if (empty($repos)) {
            $this->logger->info('Found no repo metainfo for package ' . $package->getIdentifier());
            return array();
        }
        
        /* @var $repo \Metagist\MetaInfo */
        $repoInfo = $repos->first();
        $url      = $repoInfo->getValue();
        
        $needle = '://github.com/';
        if (strpos($url, $needle) === FALSE) {
            $this->logger->info('Is no github repo:' . $url);
            return array();
        }
        
        list($owner, $repo) = $this->getOwnerAndRepoFromUrl($url);
        if (empty($owner) || empty($repo)) {
            $this->logger->info('Incomplete path in ' . $url);
            return array();
        }
        
        $metaInfos = array();
        $gitInfos = $this->fromGithubRepo($owner, $repo);
        if (is_array($gitInfos)) {
            $metaInfos = array_merge($metaInfos, $gitInfos);
        }
        
        $pageInfos = $this->fromGithubPage($owner, $repo);
        if (is_array($pageInfos)) {
            $metaInfos = array_merge($metaInfos, $pageInfos);
        }
        
        $issues = $this->fromGithubIssuePage($owner, $repo);
        if (is_array($issues)) {
            $metaInfos = array_merge($metaInfos, $issues);
        }
        
        return $metaInfos;
    }
    
    /**
     * Creates metainfos by retrieving repository data from github.
     * 
     * @param string $owner
     * @param string $repository
     * @return \Metagist\MetaInfo[]
     */
    public function fromGithubRepo($owner, $repository)
    {
        $collection = array();
        
        $stats = new Stats($this->getClient());
        $contributors = $stats->contributors($owner, $repository);
        $collection[] = MetaInfo::fromValue(MetaInfo::CONTRIBUTORS, count($contributors));
        $commitCount = 0;
        foreach ($contributors as $contributor) {
            $commitCount += $contributor['total'];
        }
        $collection[] = MetaInfo::fromValue(MetaInfo::COMMITS, $commitCount);
        
        return $collection;
    }
    
    /**
     * Reach for the stars: Scrapes info from the github page.
     * 
     * @param string $username
     * @param string $repository
     * @return \Metagist\MetaInfo[]
     */
    public function fromGithubPage($username, $repository)
    {
        $collection = array();
        $url        = '/' . urlencode($username).'/'.urlencode($repository);
        $crawler    = $this->getCrawlerWithContentsFrom($url);
        $nodes      = $crawler->filter('.social-count');
        
        foreach ($nodes as $node) {
            $starred = intval(trim($node->nodeValue));
            $collection[] = MetaInfo::fromValue(MetaInfo::STARGAZERS, $starred);
            $this->logger->info("Stargazer count fetched from $url:" . $starred);
            break;
        }
        
        return $collection;
    }
    
    /**
     * Scrapes the number of issues .
     * 
     * @param string $username
     * @param string $repository
     * @return \Metagist\MetaInfo[]
     */
    public function fromGithubIssuePage($username, $repository)
    {
        $collection = array();
        $url        = '/' . urlencode($username).'/'.urlencode($repository). '/issues';
        $crawler    = $this->getCrawlerWithContentsFrom($url);
        $nodes      = $crawler->filter('.issues-list-options');
        $nodes      = $nodes->filter('.button-group');
        $nodes      = $nodes->filter('a');
        
        foreach ($nodes as $node) {
            $content = trim($node->nodeValue);
            if (strpos($content, 'Open') !== false) {
                $content = substr($content, 0, strpos($content," "));
                $collection[] = MetaInfo::fromValue(MetaInfo::OPEN_ISSUES, $content);
                $this->logger->info("Open issues fetched from $url:" . $content);
            } elseif (strpos($content, 'Closed') !== false) {
                $content = substr($content, 0, strpos($content," "));
                $collection[] = MetaInfo::fromValue(MetaInfo::CLOSED_ISSUES, $content);
                $this->logger->info("Closed issues fetched from $url:" . $content);
            } 
        }
        
        return $collection;
    }

    /**
     * Returns a dom crawler with page contents.
     * 
     * @param string $url
     * @return \Symfony\Component\DomCrawler\Crawler
     */
    protected function getCrawlerWithContentsFrom($url)
    {
        $client     = $this->getHttpClientForScraping();
        $crawler    = new \Symfony\Component\DomCrawler\Crawler();
        try {
            $result = $client->get($url);
            /* @var $result \Github\HttpClient\Message\Response */
            $crawler->addHtmlContent($result->getContent());
        } catch (\Github\Exception\RuntimeException $exception) {
            $this->logger->alert("Error retrieving info from $url:" . $client->getLastRequest(). $client->getLastResponse());
        }
        
        return $crawler;
    }
    
    /**
     * Return the client required for scraping.
     * 
     * @return \Github\HttpClient\HttpClient;
     */
    protected function getHttpClientForScraping()
    {
        $client = $this->getClient()->getHttpClient();
        $client->setOption('base_url', $this->githubBaseUrl);
        $client->setHeaders(
            array(
                "User-Agent" => self::USER_AGENT,
                "Accept" => "text/html,application/xhtml+xml,application/xml,application/vnd.github.beta+json"
            )
        );
        return $client;
    }
    
    /**
     * Returns the configured github client.
     * 
     * @return \Github\Client
     */
    protected function getClient()
    {
        if ($this->client === null) {
            $client = new \Github\Client();
            $config = $this->application[self::GITHUB_CLIENT_CONFIG];    
            $client->authenticate(
                $config["client_id"],
                $config["client_secret"],
                \Github\Client::AUTH_URL_CLIENT_ID
            );
            $client->setHeaders(
                array("User-Agent" => self::USER_AGENT)
            );
            $this->client = $client;
        }
        
        return $this->client;
    }
    
    /**
     * Inject a github client (for testing only).
     * 
     * @param \Github\Client $client
     */
    public function setGitHubClient(\Github\Client $client)
    {
        $this->client = $client;
    }
    
    /**
     * Returns an array containing the owner name and the repo name.
     * 
     * @param string $url
     * @return array
     */
    protected function getOwnerAndRepoFromUrl($url)
    {
        $parts  =  parse_url($url);
        $owner  = null;
        $repo   = null;
        @list ($owner, $repo) = explode('/', ltrim($parts['path'], '/'));
        
        return array(
            strtolower(ltrim($owner, '/')),
            strtolower(basename($repo, '.git'))
        );
    }
}