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
        $result = $this->createQueryBuilder('p')
           ->where('p.identifier LIKE :part')
           ->setParameter('part', '%' . $identifier . '%')
           ->getQuery()
           ->getResult();
        
        return new ArrayCollection($result);
    }
    
    /**
     * Saves a package.
     * 
     * @param \Metagist\Package $package
     * @return int
     */
    public function save(Package $package)
    {
        $this->getEntityManager()->persist($package);
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