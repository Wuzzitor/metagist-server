<?php
namespace Metagist\ServerBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Metagist\Validator;
use Metagist\PackageInterface;

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
     * @param \Metagist\PackageInterface $package
     * @return \Metagist\ServerBundle\Entity\Package
     */
    public function save(PackageInterface $package)
    {
        if ($package instanceof \Metagist\Package) {
            $entity = $this->findOneBy(array('identifier' => $package->getIdentifier()));
            if ($entity === null) {
                $entity = new Package($package->getIdentifier());
            }
        } else {
            $entity = $package;
        }

        /* @var $entity Package */
        $entity->setTimeUpdated(new \DateTime());
        $entity->setDescription($package->getDescription());
        $entity->setType($package->getType());
        $entity->setVersions($package->getVersions());
        
        $this->getEntityManager()->persist($entity);
        $this->getEntityManager()->flush();
        return $entity;
    }
}