<?php

namespace Metagist\ServerBundle\Entity;

use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * User
 *
 * @ORM\Table(name="users")
 * @ORM\Entity(repositoryClass="UserRepository")
 */
class User implements UserInterface
{
    /**
     * user role
     * 
     * @var string
     */

    const ROLE_USER = 'ROLE_USER';

    /**
     * system role (remote workers)
     * 
     * @var string
     */
    const ROLE_SYSTEM = 'ROLE_SYSTEM';

    /**
     * admin role
     * 
     * @var string
     */
    const ROLE_ADMIN = 'ROLE_ADMIN';

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="username", type="string", length=32, nullable=false)
     */
    private $username;

    /**
     * @var string
     *
     * @ORM\Column(name="avatar_url", type="string", length=255, nullable=true)
     */
    private $avatarUrl;

       /**
     * Constructor.
     * 
     * @param string $username github login / nickname
     * @param string $role
     * @param string $avatarUrl
     */
    public function __construct($username, $role = null, $avatarUrl = null)
    {
        $this->username    = $username;
        $this->role        = $role;
        $this->avatarUrl   = $avatarUrl;
    }
    
    /**
     * Set the user Id.
     * 
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }
    
    /**
     * Returns the user Id.
     * 
     * @return int|null
     */
    public function getId()
    {
        return $this->id;
    }
    
    /**
     * Returns the user's role.
     * 
     * @return array
     */
    public function getRoles()
    {
        return array($this->role);
    }
    
    /**
     * Set a role for the user.
     * 
     * @param string $role
     */
    public function setRole($role)
    {
        $this->role = $role;
    }

    /**
     * Returns the username (at metagist and github).
     * 
     * @return string
     */
    public function getUsername()
    {
       return $this->username;
    }
    
    /**
     * Returns the avatar image url.
     * 
     * @return string
     */
    public function getAvatarUrl()
    {
        return $this->avatarUrl;
    }

    public function eraseCredentials()
    {
        
    }

    public function getPassword()
    {
        
    }
    
    public function getSalt()
    {
        
    }
    
    /**
     * toString returns the username.
     * 
     * @return string
     */
    public function __toString()
    {
        return $this->getUsername();
    }
}
