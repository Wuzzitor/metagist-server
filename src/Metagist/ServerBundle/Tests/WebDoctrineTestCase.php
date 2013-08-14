<?php
namespace Metagist\ServerBundle\Tests;
 
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Doctrine\DBAL\Driver\PDOSqlite\Driver as SqliteDriver;
 
/**
 * Class to test against the database.
 * 
 * @copyright (c) 2013, Miguel Angel Gabriel
 * @link http://dev4theweb.blogspot.de/2012/07/yet-another-look-at-isolated-symfony2.html
 */
abstract class WebDoctrineTestCase extends WebTestCase
{
    /**
     * entity manager instance.
     * 
     * @var \Doctrine\ORM\EntityManager
     */
    protected static $entityManager;
    
    protected static $client;
    protected static $application;
     
    protected static $isFirstTest = true;
 
    /**
     * Prepare each test
     */
    public function setUp()
    {
        parent::setUp();
 
        static::$client = static::createClient();
 
        if (!$this->useCachedDatabase()) {
            $this->databaseInit();
            $this->loadFixtures();  
        }
    }
 
    /**
     * Initialize database
     */
    protected function databaseInit()
    {
        static::$entityManager = static::$kernel
            ->getContainer()
            ->get('doctrine.orm.entity_manager');
 
        static::$application = new \Symfony\Bundle\FrameworkBundle\Console\Application(static::$kernel);
         
        static::$application->setAutoExit(false);
        $this->runConsole("doctrine:schema:drop", array("--force" => true));
        $this->runConsole("doctrine:schema:create");
        $this->runConsole("cache:warmup");
    }
 
    /**
     * Load tests fixtures
     */
    protected function loadFixtures()
    {
        $this->runConsole("doctrine:fixtures:load");
    }
     
    /**
     * Use cached database for testing or return false if not
     */
    protected function useCachedDatabase()
    {
        $container = static::$kernel->getContainer();
        $registry = $container->get('doctrine');
        $om = $registry->getManager();
        $connection = $om->getConnection();
         
        if ($connection->getDriver() instanceOf SqliteDriver) {
            $params = $connection->getParams();
            $name = isset($params['path']) ? $params['path'] : $params['dbname'];
            $filename = pathinfo($name, PATHINFO_BASENAME);
            $backup = $container->getParameter('kernel.cache_dir') . '/'.$filename;
 
            // The first time we won't use the cached version
            if (self::$isFirstTest) {
                self::$isFirstTest = false;
                return false;
            }
             
            self::$isFirstTest = false;
 
            // Regenerate not-existing database
            if (!file_exists($name)) {
                @unlink($backup);
                return false;
            }
 
            $om->flush();
            $om->clear();
             
            // Copy backup to database
            if (!file_exists($backup)) {
                copy($name, $backup);
            }
 
            copy($backup, $name);
            return true;
        }
         
        return false;
    }
 
    /**
     * Executes a console command
     *
     * @param type $command
     * @param array $options
     * @return type integer
     */
    protected function runConsole($command, Array $options = array())
    {
        $options["--env"] = "test";
        $options["--quiet"] = null;
        $options["--no-interaction"] = null;
        $options = array_merge($options, array('command' => $command));
        return static::$application->run(new \Symfony\Component\Console\Input\ArrayInput($options));
    }
}