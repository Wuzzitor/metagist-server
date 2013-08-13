<?php
namespace Metagist\ServerBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Metagist\ServerBundle\Resources\Validator;

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
        return $this->findBy(array('package' => $package));
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
        
        return $this->findBy(array('group' => $group));
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
        
        //delete old entries
        $this->connection->executeQuery(
            'DELETE FROM metainfo WHERE package_id = ?',
            array($package->getId())
        );
            
        $metaInfos = $package->getMetaInfos();
        foreach ($metaInfos as $info) {
            $this->save($info, null);
        }
    }
    
    /**
     * Saves (inserts) a single info.
     * 
     * @param \Metagist\MetaInfo $info
     * @param mixed              $cardinality
     * @return int
     */
    public function save(MetaInfo $info, $cardinality)
    {
        if ($cardinality === 1) {
            $this->connection->executeQuery(
                'DELETE FROM metainfo WHERE package_id = ? AND `group` = ?',
                array(
                    $info->getPackage()->getId(),
                    $info->getGroup()
                )
            );
        }
        
        $stmt = $this->connection->executeQuery(
            'INSERT INTO metainfo (package_id, user_id, time_updated, version, `group`, value) 
             VALUES (?, ?, ?, ?, ?, ?)',
            array(
                $info->getPackage()->getId(),
                $info->getUserId(),
                date('Y-m-d H:i:s', time()),
                $info->getVersion(),
                $info->getGroup(),
                $info->getValue()
            )
        );
        
        return $stmt->rowCount();
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
        
        return $builder->getQuery()->execute();
    }
    
    /**
     * Creates a MetaInfo instance with a dummy package based on the results
     * of a joined query.
     * 
     * @param array $data
     * @return MetaInfo
     */
    protected function createMetaInfoWithDummyPackage(array $data)
    {
        $package = new Package($data['identifier'], $data['package_id']);
        $data['package'] = $package;
        $metainfo = MetaInfo::fromArray($data);
        return $metainfo;
    }
}