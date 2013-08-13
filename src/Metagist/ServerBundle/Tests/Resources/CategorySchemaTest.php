<?php
namespace Metagist\ServerBundle\Tests\Resources;

use Metagist\ServerBundle\Resources\CategorySchema;

/**
 * Tests the category schema class.
 * 
 * @author Daniel Pozzi <bonndan76@googlemail.com>
 */
class CategorySchemaTest extends \PHPUnit_Framework_TestCase
{
    /**
     * system under test
     * 
     * @var CategorySchema
     */
    private $schema;

    /**
     * Test setup.
     */
    public function setUp()
    {
        parent::setUp();

        $json = file_get_contents(__DIR__ . '/testdata/testcategories.json');
        $this->schema = new CategorySchema($json);
    }

    /**
     * Ensures the constructor checks the json.
     */
    public function testConstructorThrowsException()
    {
        $this->setExpectedException('\InvalidArgumentException');
        $this->schema = new CategorySchema('');
    }

    /**
     * Ensures the constructor checks the json.
     */
    public function testGroupNamesMustBeUnique()
    {
        $json = file_get_contents(__DIR__ . '/testdata/notunique.json');
        $this->setExpectedException('\Metagist\Exception');
        $this->schema = new CategorySchema($json);
    }

    /**
     * Tests the factory method.
     */
    public function testCreate()
    {
        $schema = CategorySchema::create();
        $this->assertInstanceOf("\Metagist\CategorySchema", $schema);
    }

    /**
     * Ensures categories are returned.
     */
    public function testGetCategories()
    {
        $cats = $this->schema->getCategories();
        $this->assertNotNull($cats);
    }

    /**
     * Ensures groups are returned.
     */
    public function testGetGroups()
    {
        $groups = $this->schema->getGroups('test');
        $this->assertNotNull($groups);
        $this->assertInternalType('array', $groups);
    }

    /**
     * Ensures the category access is returned.
     */
    public function testGetAccessForCategory()
    {
        $role = $this->schema->getAccess('test');
        $this->assertEquals('ROLE_ADMIN', $role);
    }

    /**
     * Ensures the category is checked.
     */
    public function testGetAccessForCategoryException()
    {
        $this->setExpectedException('\InvalidArgumentException');
        $this->schema->getAccess('nonono');
    }

    /**
     * Ensures the group access is returned.
     */
    public function testGetAccessForGroup()
    {
        $role = $this->schema->getAccess('test', 'testBoolean');
        $this->assertEquals('ROLE_SYSTEM', $role);
    }

    /**
     * Ensures the group is checked.
     */
    public function testGetAccessForGroupException()
    {
        $this->setExpectedException('\InvalidArgumentException');
        $this->schema->getAccess('test', 'nonono');
    }

    /**
     * Ensures a category can be found by a group
     */
    public function testGetCategoryForGroup()
    {
        $category = $this->schema->getCategoryForGroup('testInteger');
        $this->assertEquals('test', $category);
    }

    /**
     * Ensures an exception is thrown if the group name is not configured.
     */
    public function testGetCategoryForGroupException()
    {
        $this->setExpectedException('\InvalidArgumentException');
        $this->schema->getCategoryForGroup('nonono');
    }

}