<?php

namespace Metagist\ServerBundle\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Doctrine\ORM\Tools\SchemaTool;
/**
 * Class to test against the database using sqlite.
 * 
 * @link http://www.theodo.fr/blog/2011/09/symfony2-unit-database-tests/
 */
abstract class WebDoctrineTestCase extends WebTestCase
{

    /**
     * entity manager instance.
     * 
     * @var \Doctrine\ORM\EntityManager
     */
    protected $entityManager;
    protected static $client;

    /**
     * Prepare each test
     */
    public function setUp()
    {
        parent::setUp();

        static::$client = static::createClient();

        $this->databaseInit();
        $this->loadFixtures();
    }

    /**
     * Initialize database
     */
    protected function databaseInit()
    {
        $this->entityManager = static::$kernel->getContainer()
            ->get('doctrine.orm.entity_manager');
        $this->generateSchema();
    }

    /**
     * Load tests fixtures
     */
    protected function loadFixtures()
    {
        
    }

    protected function generateSchema()
    {
        // Get the metadata of the application to create the schema.
        $metadata = $this->entityManager->getMetadataFactory()->getAllMetadata();

        if (!empty($metadata)) {
            // Create SchemaTool
            $tool = new SchemaTool($this->entityManager);
            $tool->createSchema($metadata);
            echo 'Schema created.';
        } else {
            throw new Doctrine\DBAL\Schema\SchemaException('No Metadata Classes to process.');
        }
    }
}