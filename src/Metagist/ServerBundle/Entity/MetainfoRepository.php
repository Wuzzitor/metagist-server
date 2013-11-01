<?php
namespace Metagist\ServerBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Metagist\ServerBundle\Validation\Validator;

/**
 * Repository for package meta information.
 * 
 * @author Daniel Pozzi <bonndan76@googlemail.com>
 */
class MetainfoRepository extends EntityRepository
{
    /**
     * a meta info validator instance
     * 
     * @var Validator
     */
    private $validator;
    
    /**
     * Inject the validator.
     * 
     * @param Validator                        $validator
     */
    public function setValidator(Validator $validator)
    {
        $this->validator  = $validator;
    }
    
    /**
     * Retrieves all stored meta info for the given package.
     * 
     * @param \Metagist\Package $package
     * @return \Doctrine\Common\Collections\Collection
     */
    public function byPackage(Package $package)
    {
        $result = $this->findBy(array('package' => $package));
        return new ArrayCollection($result);
    }
    
    /**
     * Returns a collection of metainfos with dummy packages.
     * 
     * @param string $category
     * @param string $group
     * @return \Doctrine\Common\Collections\Collection
     */
    public function byGroup($group)
    {
        if (!$this->validator->isValidGroup($group)) {
            throw new \InvalidArgumentException('Group not existing.');
        }
        
        $result = $this->findBy(array('group' => $group));
        return new ArrayCollection($result);
    }
    
    /**
     * Saves a package.
     * 
     * @param \Metagist\Package $package
     * @throws \RuntimeException
     */
    public function savePackage(Package $package)
    {
        if ($package->getId() == null) {
            throw new \RuntimeException('Save the package first.');
        }
        
        $metaInfos = $package->getMetaInfos();
        foreach ($metaInfos as $info) {
            $this->save($info, null);
        }
    }
    
    /**
     * Saves (inserts) a single info.
     * 
     * @param \Metagist\MetaInfo $info
     * @return int
     * @todo remove
     */
    public function save(MetaInfo $info)
    {
        $this->getEntityManager()->persist($info);
        $this->getEntityManager()->flush();
    }
    
    /**
     * Retrieves metainfo that has been updated lately.
     * 
     * @param int $limit
     * @return \Doctrine\Common\Collections\ArrayCollection
     * @todo parameter binding did not work.
     */
    public function latest($limit = 25)
    {
        $builder = $this->createQueryBuilder('m')
            ->orderBy('m.timeUpdated', 'DESC')
            ->setMaxResults($limit);
        
        return new ArrayCollection($builder->getQuery()->execute());
    }
}