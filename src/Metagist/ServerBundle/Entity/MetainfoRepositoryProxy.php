<?php
namespace Metagist\ServerBundle\Entity;

use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Metagist\ServerBundle\Validation\CategorySchema;
    
/**
 * Security proxy for the metainfo repo.
 * 
 * @author Daniel Pozzi <bonndan76@googlemail.com>
 */
class MetainfoRepositoryProxy
{
    /**
     * MetaInfo Repo
     * 
     * @var \Metagist\MetaInfoRepository 
     */
    private $repository;
    
    /**
     * The security context (controls access).
     * 
     * @var \Symfony\Component\Security\Core\SecurityContextInterface
     */
    private $context;
    
    /**
     * The category schema
     * 
     * @var \Metagist\ServerBundle\Validation\CategorySchema
     */
    private $schema;
    
    private $checkSecurity = true;
    
    /**
     * Constructor.
     * 
     * @param MetainfoRepository $repo
     * @param \Symfony\Component\Security\Core\SecurityContextInterface $context
     * @param \Metagist\ServerBundle\Resources\CategorySchema $schema
     */
    public function __construct(
        MetaInfoRepository $repo,
        SecurityContextInterface $context,
        CategorySchema $schema
    ) {
        $this->repository = $repo;
        $this->context    = $context;
        $this->schema     = $schema;
    }

    /**
     * Forwarding method.
     * 
     * @param string $name
     * @param array  $arguments
     */
    public function __call($name, $arguments)
    {
        return call_user_func_array(array($this->repository, $name), $arguments);
    }
    
    public function disableSecurity()
    {
        $this->checkSecurity = false;
    }
    
    /**
     * Controls the access to the save() method.
     * 
     * @param \Metagist\MetaInfo $metaInfo
     * @throws AccessDeniedException
     */
    public function save(MetaInfo $metaInfo)
    {
        $group      = $metaInfo->getGroup();
        $category   = $this->schema->getCategoryForGroup($group);
        $this->checkPermission($category, $group);
        
        //cardinality check
        $groups      = $this->schema->getGroups($category);
        $groupData   = $groups[$group];
        $cardinality = isset($groupData->cardinality) ? $groupData->cardinality : null;
        
        $this->repository->save($metaInfo, $cardinality);
    }
    
    /**
     * Returns the cardinality for a group.
     * 
     * @param \Metagist\ServerBundle\Entity\MetaInfo $metaInfo
     * @return int|null
     */
    private function getCardinality(MetaInfo $metaInfo)
    {
        $group      = $metaInfo->getGroup();
        $category   = $this->schema->getCategoryForGroup($group);
        $groups     = $this->schema->getGroups($category);
        $groupData  = $groups[$group];
        
        return isset($groupData->cardinality) ? $groupData->cardinality : null;
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
            $this->save($info, $this->getCardinality($info));
        }
    }
    
    private function checkPermission($category, $group)
    {
        if (!$this->checkSecurity) {
            return;
        }
        
        $reqRole    = $this->schema->getAccess($category, $group);
        if (!$this->context->isGranted($reqRole)) {
            $token = $this->context->getToken();
            throw new AccessDeniedException(
                $token->getUsername() . ' is not authorized to save ' . $category . "/" . $group . ', required is ' . $reqRole
            );
        }
    }
}