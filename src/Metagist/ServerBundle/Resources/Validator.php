<?php
namespace Metagist\ServerBundle\Resources;

/**
 * Validator class.
 * 
 * @author Daniel Pozzi <bonndan76@googlemail.com>
 */
class Validator
{
    /**
     * category schema
     * @var CategorySchema 
     */
    private $schema;
    
    /**
     * Constructor.
     * 
     * @param CategorySchema $categorySchema
     * @throws \InvalidArgumentException
     */
    public function __construct(CategorySchema $categorySchema)
    {
        $this->schema = $categorySchema;
    }
    
    /**
     * Checks if the category group exists.
     * 
     * @param string $category
     * @param string $group
     * @return boolean
     */
    public function isValidCategoryGroup($category, $group)
    {
        try {
            $groups = $this->schema->getGroups($category);
            return array_key_exists($group, $groups);
        } catch (\InvalidArgumentException $exception) {
            return false;
        }
    }
    
    /**
     * Checks if the group exists.
     * 
     * @param string $group
     * @return boolean
     */
    public function isValidGroup($group)
    {
        if (!self::isValidName($group)) {
            return false;
        }
        try {
            $this->schema->getCategoryForGroup($group);
            return true;
        } catch (\InvalidArgumentException $exception) {
            return false;
        }
    }
    
    /**
     * Validate a name (author or package name).
     * 
     * @param string $name author or package name
     * @return boolean
     */
    public static function isValidName($name)
    {
        $pattern = '/^[a-zA-Z0-9\-]{2,128}$/i';
        return (bool) preg_match($pattern, $name);
    }
    
    /**
     * Checks if a package identifier has the correct format.
     * 
     * @param string $identifier
     * @return boolean
     */
    public static function isValidIdentifier($identifier)
    {
        $pieces = Util::splitIdentifier($identifier);
        $slashPos = strpos($identifier, '/');
        if (in_array($slashPos, array(FALSE, 0, strlen($identifier)-1), true)) {
            return false;
        }
        
        list($author, $name) = $pieces;
        return self::isValidName($author) && self::isValidName($name);
    }
    
    /**
     * Validates a metainfo object against the category schema.
     * 
     * @param MetaInfo $metaInfo
     * @return boolean
     * @throws InvalidInfoException
     */
    public function isValidMetaInfo(MetaInfo $metaInfo)
    {
        if ($metaInfo->getPackage() == null || $metaInfo->getGroup() == null) {
            throw new InvalidInfoException('Package or group is not set.');
        }
        
        $type  = $this->schema->getType($metaInfo->getGroup());
        $value = $metaInfo->getValue();
        
        if ($type == CategorySchema::TYPE_STRING) {
            return is_string($value) && !empty($value);
        }
        
        if ($type == CategorySchema::TYPE_BOOLEAN) {
            return (bool)filter_var($value, FILTER_VALIDATE_BOOLEAN);
        }
        
        if ($type == CategorySchema::TYPE_INTEGER) {
            return (bool)filter_var($value, FILTER_VALIDATE_INT);
        }
        
        if ($type == CategorySchema::TYPE_URL || $type == CategorySchema::TYPE_BADGE) {
            return (bool)filter_var($value, FILTER_VALIDATE_URL);
        }
        
        return false;
    }
}