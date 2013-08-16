<?php
namespace Metagist\ServerBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Metagist\ServerBundle\Entity\User;

/**
 * Repository for users
 * 
 * @author Daniel Pozzi <bonndan76@googlemail.com>
 */
class UserRepository extends EntityRepository
{
    /**
     * Saves the given user.
     * 
     * @param \Metagist\ServerBundle\Entity\User $user
     */
    public function save(User $user)
    {
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }
}