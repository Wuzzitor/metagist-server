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
     * Saves (inserts) a single info.
     * 
     * @param \Metagist\MetaInfo $info
     * @param bool $replace replaced same metainfos
     */
    public function save(MetaInfo $info, $replace = false)
    {
        $entityManger = $this->getEntityManager();
        if ($replace === true) {
            $toDelete = $this->findBy(array(
                'package' => $info->getPackage(),
                'group' => $info->getGroup()
            ));
            foreach ($toDelete as $oldInfo) {
                $entityManger->remove($oldInfo);
            }
        }
        
        $entityManger->persist($info);
        $entityManger->flush();
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