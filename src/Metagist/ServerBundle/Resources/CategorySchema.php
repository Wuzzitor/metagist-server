<?php
namespace Metagist\ServerBundle\Resources;

use Metagist\ServerBundle\Exception;

/**
 * Class representing the contents of the metainfo.json file.
 * 
 * @author Daniel Pozzi <bonndan76@googlemail.com>
 */
class CategorySchema
{
    /**
     * string type
     * @var string
     */
    CONST TYPE_STRING = 'string';
    
    /**
     * boolean type
     * @var string
     */
    CONST TYPE_BOOLEAN = 'boolean';
    
    /**
     * integer type
     * @var string
     */
    CONST TYPE_INTEGER = 'integer';
    
    /**
     * url type
     * @var string
     */
    CONST TYPE_URL = 'url';
    
    /**
     * badge type (image url)
     * @var string
     */
    CONST TYPE_BADGE = 'badge';
    
    /**
     * @var object
     */
    private $categories;
    
    /**
     * Creates a schema instance with the default config file contents.
     * 
     * @return \Metagist\CategorySchema
     */
    public static function create()
    {
        $config = __DIR__ . '/public/metainfo.json';
        return new static(file_get_contents($config));
    }
    
    /**
     * Initialize with a json string.
     * 
     * @param string $json
     * @throws \InvalidArgumentException is the categories are null
     */
    public function __construct($json)
    {
        $this->categories = json_decode($json);
        if ($this->categories === null) {
            throw new \InvalidArgumentException('Invalid json passed?');
        }
        $this->assertGroupsAreUnique();
    }
    
    /**
     * Returns the type of a group.
     * 
     * @param string $group
     * @return string
     * @throws \InvalidArgumentException
     */
    public function getType($group)
    {
        $category = $this->getCategoryForGroup($group);
        $this->assertGroupExists($category, $group);
        return $this->categories->$category->types->$group->type;
    }
    
    /**
     * Returns the role which can access the category or group.
     * 
     * @param string $category
     * @param string $group
     * @return string
     * @throws \InvalidArgumentException
     */
    public function getAccess($category, $group = null)
    {
        if ($group === null) {
            $this->assertCategoryExists($category);
            return $this->categories->$category->access;
        }
        
        $this->assertGroupExists($category, $group);
        return $this->categories->$category->types->$group->access;
    }
    
    /**
     * Returns the categories, iterable.
     * 
     * @return array
     */
    public function getCategories()
    {
        return (array)$this->categories;
    }
    
    /**
     * Returns the groups of a category.
     * 
     * @param string $category
     * @return array
     */
    public function getGroups($category)
    {
        $this->assertCategoryExists($category);
        return (array)$this->categories->$category->types;
    }
    
    /**
     * Reverse search based on group name.
     * 
     * @param string $group
     * @return string|false
     */
    public function getCategoryForGroup($group)
    {
        foreach (array_keys($this->getCategories()) as $category) {
            $groups = $this->getGroups($category);
            foreach (array_keys($groups) as $groupName) {
                if ($group == $groupName) {
                    return $category;
                }
            }
        }
        
        throw new \InvalidArgumentException('Unknown group: ' . $group);
    }
    
    /**
     * Asserts a category exists.
     * 
     * @param string $category
     * @throws \InvalidArgumentException
     */
    protected function assertCategoryExists($category)
    {
        if (!isset($this->categories->$category)) {
            throw new \InvalidArgumentException('Unknown category: ' . $category);
        }
    }
    
    /**
     * Asserts a group exists.
     * 
     * @param string $category
     * @param string $group
     * @throws \InvalidArgumentException
     */
    protected function assertGroupExists($category, $group)
    {
        $this->assertCategoryExists($category);
        
        if (!isset($this->categories->$category->types->$group)) {
            throw new \InvalidArgumentException('Unknown group: ' . $group);
        }
    }
    
    /**
     * Ensures the group names are only used once.
     * 
     * @throws Exception
     */
    protected function assertGroupsAreUnique()
    {
        $groupsNames = array();
        
        foreach (array_keys($this->getCategories()) as $category) {
            $groups = $this->getGroups($category);
            foreach (array_keys($groups) as $groupName) {
                if (isset($groupsNames[$groupName])) {
                    throw new Exception('Group name not unique: ' . $category . '/' . $groupName);
                }
                $groupsNames[$groupName] = $groupName;
            }
        }
    }
}