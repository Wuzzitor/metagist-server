<?php

namespace Metagist\ServerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Ratings
 *
 * @ORM\Table(name="ratings")
 * @ORM\Entity(repositoryClass="RatingRepository")
 */
class Rating
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="time_updated", type="datetime", nullable=false)
     */
    private $timeUpdated;

    /**
     * @var string
     *
     * @ORM\Column(name="version", type="string", length=32, nullable=true)
     */
    private $version;

    /**
     * @var integer
     *
     * @ORM\Column(name="rating", type="integer", nullable=false)
     */
    private $rating;

    /**
     * @var string
     *
     * @ORM\Column(name="comment", type="text", nullable=false)
     */
    private $comment;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="text", nullable=false)
     */
    private $title;

    /**
     * @var \Users
     *
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     * })
     */
    private $user;

    /**
     * Related package.
     * 
     * @var \Package
     * @ORM\ManyToOne(targetEntity="Package")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="package_id", referencedColumnName="id")
     * })
     */
    private $package;

    /**
     * Factory method.
     * 
     * @param array $data
     * @return Rating
     */
    public static function fromArray(array $data)
    {
        $info = new self();
        foreach ($data as $key => $value) {
            if (!property_exists($info, $key)) {
                continue;
            }
            $info->$key = $value;
        }
        
        return $info;
    }
    
    /**
     * Set the related package.
     * 
     * @param Package $package
     */
    public function setPackage(Package $package)
    {
        $this->package = $package;
    }
    
    /**
     * Returns the related package.
     * 
     * @return Package|null
     */
    public function getPackage()
    {
        return $this->package;
    }
    
    /**
     * Returns the value.
     * 
     * @return string|int
     */
    public function getRating()
    {
        return $this->rating;
    }
    
    /**
     * Returns the title.
     * 
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }
    
    /**
     * Returns the comment text.
     * 
     * @return string|null
     */
    public function getComment()
    {
        return $this->comment;
    }
    
    /**
     * Returns the associated version.
     * 
     * @return string|null
     */
    public function getVersion()
    {
        return $this->version;
    }
    
    /**
     * Returns the id of the user who created the info.
     * 
     * @return int|null
     */
    public function getUserId()
    {
        if ($this->user_id != null) {
            return $this->user_id;
        }
        
        if ($this->user !== null) {
            return $this->user->getId();
        }
        
        return null;
    }
    
    /**
     * Returns the time of the last update
     * 
     * @return string|null
     */
    public function getTimeUpdated()
    {
        return $this->time_updated;
    }
    
    /**
     * Set the user.
     * 
     * @param \Metagist\User $user
     */
    public function setUser(User $user)
    {
        $this->user = $user;
    }
    
    /**
     * Returns the user.
     * 
     * @return User|null
     */
    public function getUser()
    {
        return $this->user;
    }
}