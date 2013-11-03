<?php

namespace Metagist\ServerBundle\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityRepository;
use InvalidArgumentException;
use Metagist\ServerBundle\Entity\Package;
use Metagist\ServerBundle\Validation\Validator;

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
        $this->validator = $validator;
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
        if (!$this->validator->isValidName($author)) {
            throw new InvalidArgumentException('The author name ' . $author . ' is invalid.');
        }
        if (!$this->validator->isValidName($name)) {
            throw new InvalidArgumentException('The package name ' . $name . ' is invalid.');
        }

        return $this->findOneBy(array('identifier' => $author . '/' . $name));
    }

    /**
     * Retrieves all packages matching an identifier part.
     * 
     * @param string $identifier
     * @return ArrayCollection
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
     * Selects random packages.
     * 
     * @todo assumes there is no gap in ids.
     * @param int $limit
     * @return Package
     */
    public function random($limit)
    {
        $query = $this->getEntityManager()->createQuery("select max(p.id) from MetagistServerBundle:Package p");
        $result = $query->getSingleResult();
        $highest = $result[1];
        $limit = min($highest, $limit);
        $ids = array();

        while (count($ids) < $limit) {
            $ids[] = rand(1, $highest);
            $ids = array_unique($ids);
        }

        if (count($ids) == 0) {
            return array();
        }

        return $this->findBy(array('id' => $ids));
    }

    /**
     * Returns the packages which are not categorized yet.
     * 
     * @return Package[]
     */
    public function uncategorized($limit = 100)
    {
        $packages = $this->findBy(array(), array(), $limit);
        foreach ($packages as $key => $package) {
            if ($package->getCategories()->count() > 0) {
                unset($packages[$key]);
            }
        }
        
        return $packages;
    }

    /**
     * Saves a package.
     * 
     * @param Package $package
     * @return Package
     */
    public function save(Package $package)
    {
        $package->setTimeUpdated(new DateTime());
        $this->getEntityManager()->persist($package);
        $this->getEntityManager()->flush();
        return $package;
    }

    /**
     * Returns all the package dependencies as packge instance.
     * 
     * @param \Metagist\ServerBundle\Entity\Package $package
     * @return \Metagist\ServerBundle\Entity\Package[]
     */
    public function getPackageDependencies(Package $package)
    {
        $deps = $package->getDependencies();
        $identifiers = array();
        foreach ($deps as $dep) {
            $identifiers[] = $dep->getDependencyIdentifier();
        }
        if (empty($identifiers)) {
            return array();
        }
        
        return $this->findBy(array('identifier' => $identifiers));
    }
}
