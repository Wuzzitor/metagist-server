<?php
namespace Metagist\ServerBundle\Entity;

use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Metagist\CategorySchema;
    
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
     * @var \Metagist\CategorySchema
     */
    private $schema;
    
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
        $reqRole    = $this->schema->getAccess($category, $group);
        if (!$this->context->isGranted($reqRole)) {
            $token = $this->context->getToken();
            throw new AccessDeniedException(
                $token->getUsername() . ' is not authorized to save ' . $category . "/" . $group . ', required is ' . $reqRole
            );
        }
        
        //cardinality check
        $groups      = $this->schema->getGroups($category);
        $groupData   = $groups[$group];
        $cardinality = isset($groupData->cardinality) ? $groupData->cardinality : null;
        
        $this->repository->save($metaInfo, $cardinality);
    }
}