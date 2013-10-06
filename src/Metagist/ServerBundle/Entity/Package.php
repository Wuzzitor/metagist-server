<?php
namespace Metagist\ServerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Metagist\Validator;
use Metagist\PackageInterface;
use Doctrine\ORM\Mapping\UniqueConstraint;

/**
 * Package
 *
 * @ORM\Table(name="packages",uniqueConstraints={@UniqueConstraint(name="identifier_idx", columns={"identifier"})})
 * @ORM\Entity(repositoryClass="PackageRepository")
 */
class Package implements PackageInterface
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
     * @var string
     *
     * @ORM\Column(name="identifier", type="string", length=255, nullable=false)
     */
    private $identifier;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=false)
     */
    private $description;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=64, nullable=false)
     */
    private $type = "library";

    /**
     * @var string
     * @ORM\Column(name="versions", type="text", nullable=false)
     */
    private $versions = '';

    /**
     * time of last update
     * 
     * @var \DateTime
     * @ORM\Column(name="time_updated", type="datetime", nullable=false)
     */
    private $timeUpdated;
    
    /**
     * related metainfos
     * 
     * @var Metainfo[]
     * @ORM\OneToMany(targetEntity="Metainfo", mappedBy="package",cascade={"persist"})
     */
    private $metainfos;
    
    /**
     * related dependencies
     * 
     * @var Dependency[]
     * @ORM\OneToMany(targetEntity="Dependency", mappedBy="package",cascade={"persist"})
     */
    private $dependencies;
    
    /**
     * overall rating (average or the like)
     * 
     * @var float
     * @ORM\Column(name="overall_rating", type="decimal", precision=2, scale=1)
     */
    private $overallRating = 0.0;

    /**
     * related image
     * 
     * @var Image
     * @ORM\OneToOne(targetEntity="Image", mappedBy="package",cascade={"persist"})
     */
    private $image;
    
    /**
     * Constructor.
     * 
     * @param string  $identifier
     */
    public function __construct($identifier)
    {
        $this->identifier  = $identifier;
        $this->timeUpdated = new \DateTime();
    }
    
    /**
     * Returns the id of the package.
     * 
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }
    
    /**
     * Set the id of the package.
     * 
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }
    
    /**
     * Returns the identifier of the package.
     * 
     * @return string
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }
    
    /**
     * Returns the author/owner part of the package identifier.
     * 
     * @return boolean
     */
    public function getAuthor()
    {
        if (!Validator::isValidIdentifier($this->identifier)) {
            return false;
        }
        $pieces = self::splitIdentifier($this->identifier);
        return $pieces[0];
    }
    
    /**
     * Returns the name part of the package identifier.
     * 
     * @return string|false
     */
    public function getName()
    {
        if (!Validator::isValidIdentifier($this->identifier)) {
            return false;
        }
        $pieces = self::splitIdentifier($this->identifier);
        return $pieces[1];
    }
    
    /**
     * Get the description.
     * 
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set the description.
     * 
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * Set the known versions.
     * 
     * @param array $versions
     */
    public function setVersions(array $versions)
    {
        $this->versions = implode(',', $versions);
    }
    
    /**
     * Returns all known versions.
     * 
     * @return string[]
     */
    public function getVersions()
    {
        return explode(',', $this->versions);
    }
    
    /**
     * Type setter
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }
    
    /**
     * Returns the type of the package.
     * 
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }
    
    /**
     * Set the related metainfos.
     * 
     * @param type $metainfos
     */
    public function setMetaInfos($metainfos)
    {
        foreach ($metainfos as $metainfo) {
            $metainfo->setPackage($this);
        }
        
        $this->metainfos = $metainfos;
    }
    
    /**
     * Returns the associated metainfos.
     * 
     * @param string $group
     * @return \Doctrine\Common\Collections\Collection|null
     */
    public function getMetaInfos($group = null)
    {
        if ($group !== null) {
            $callback = function (MetaInfo $metainfo) use ($group) {
                return $metainfo->getGroup() == $group; 
            };
            return $this->metainfos->filter($callback);
        }
        
        return $this->metainfos;
    }
    
    /**
     * Returns the time of the last update
     * 
     * @return string|null
     */
    public function getTimeUpdated()
    {
        return $this->timeUpdated;
    }
    
    /**
     * Set the time of the last update
     * 
     * @return \DateTime|null
     */
    public function setTimeUpdated(\DateTime $time)
    {
        $this->timeUpdated = $time;
    }
    
    /**
     * toString returns the identifier.
     * 
     * @return string
     */
    public function __toString()
    {
        return substr($this->identifier, strpos($this->identifier, '/') + 1);
    }
    
    /**
     * Returns author + name from a package identifier string.
     * 
     * @param string $identifier
     * @return array
     * @throws \InvalidArgumentException
     */
    public static function splitIdentifier($identifier)
    {
        if (!is_string($identifier)) {
            throw new \InvalidArgumentException('Identifier must be a string.');
        }
        
        return explode('/', $identifier);
    }
    
    /**
     * Returns the required dependencies.
     * 
     * @return Dependency[]
     */
    public function getDependencies()
    {
        return $this->dependencies;
    }

    /**
     * Set the required dependencies
     * 
     * @param array $dependencies
     */
    public function setDependencies($dependencies)
    {
        foreach ($dependencies as $dependency) {
            $dependency->setPackage($this);
        }
        
        $this->dependencies = $dependencies;
    }
    
    /**
     * Returns the computed overall rating
     * 
     * @return float
     */
    public function getOverallRating()
    {
        return $this->overallRating;
    }

    /**
     * Set the overall rating.
     * 
     * @param float $overallRating
     * @throws \OutOfBoundsException
     */
    public function setOverallRating($overallRating)
    {
        if ($overallRating > 5 || $overallRating < 0) {
            throw new \OutOfBoundsException('The overall rating is out of limits.');
        }
        $this->overallRating = $overallRating;
    }


}
