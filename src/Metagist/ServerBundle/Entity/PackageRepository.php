<?php
namespace Metagist\ServerBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Metagist\ServerBundle\Resources\Validator;

/**
 * Repository for packages.
 * 
 * @author Daniel Pozzi <bonndan76@googlemail.com>
 */
class PackageRepository extends EntityRepository
{
    /**
     * validator instance
     * 
     * @var Validator 
     */
    private $validator;
    
    /**
     * Inject the validator.
     * 
     * @param Validator $validator
     */
    public function setValidator(Validator $validator)
    {
        $this->validator  = $validator;
    }
    
    /**
     * Retrieve a package by author and name.
     * 
     * @param string $author
     * @param string $name
     * @return Package|null
     */
    public function byAuthorAndName($author, $name)
    {
        if (!$this->validator->isValidName($author) || !$this->validator->isValidName($name)) {
            throw new \InvalidArgumentException('The author or package name is invalid.');
        }
        
        return $this->findOneBy(array('identifier' => $author . '/' . $name));
    }
    
    /**
     * Retrieves all packages matching an identifier part.
     * 
     * @param string $identifier
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function byIdentifierPart($identifier)
    {
        $stmt = $this->connection->executeQuery(
            'SELECT * FROM packages WHERE identifier LIKE ?',
            array('%' . $identifier . '%')
        );

        $collection = new ArrayCollection();
        while ($data = $stmt->fetch()) {
            $collection->add($this->createPackageFromData($data));
        }
        
        return $collection;
    }
    
    /**
     * Saves a package.
     * 
     * @param \Metagist\Package $package
     * @return int
     */
    public function save(Package $package)
    {
        $id = $package->getId();
        $data = array(
            $package->getIdentifier(),
            $package->getDescription(),
            implode(',', $package->getVersions()),
            $package->getType(),
            date('Y-m-d H:i:s')
        );
        if ($id == null) {
            $stmt = $this->connection->executeQuery(
                'INSERT INTO packages (identifier, description, versions, type, time_updated)
                 VALUES (?, ?, ?, ?, ?)',
                $data
            );
            $id = $this->connection->lastInsertId();
            $package->setId($id);
        } else {
            $data[] = $id;
            $stmt = $this->connection->executeQuery(
                'UPDATE packages 
                 SET identifier = ?, description = ?, versions = ?, type = ?, time_updated = ?
                 WHERE id = ?',
                $data
            );
        }
        
        return $stmt->rowCount();
    }
    
    /**
     * Creates a package instance from fetched data.
     * 
     * @param array $data
     * @return \Metagist\Package
     */
    protected function createPackageFromData(array $data)
    {
        $package = new Package($data['identifier'], $data['id']);
        $package->setDescription($data['description']);
        $package->setVersions(explode(',', $data['versions']));
        $package->setType($data['type']);
        $package->setTimeUpdated($data['time_updated']);
        
        return $package;
    }
}