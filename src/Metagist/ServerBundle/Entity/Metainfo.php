<?php
namespace Metagist\ServerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Metagist\MetainfoInterface;

/**
 * Metainfo
 *
 * @ORM\Table(name="metainfo")
 * @ORM\Entity(repositoryClass="MetainfoRepository")
 */
class Metainfo implements MetainfoInterface
{
    /**
     * identifier for the repo
     * 
     * @var string
     */
    const REPOSITORY = 'repository';
    
    /**
     * identifier for the package homepage
     * 
     * @var string
     */
    const HOMEPAGE = 'homepage';
    
    /**
     * identifier for number of maintainers
     * 
     * @var string
     */
    const MAINTAINERS = 'maintainers';
    
    /**
     * identifier for number of dependencies
     * 
     * @var string
     */
    const REQUIRES = 'requires';
    
    /**
     * identifier for number of dependencies for development
     * 
     * @var string
     */
    const REQUIRES_DEV = 'requires.dev';
    
    /**
     * identifier for license type
     * 
     * @var string
     */
    const LICENSE = 'license';
    
    /**
     * Identifier for number of github stargazers.
     * 
     * @var string
     */
    const STARGAZERS = 'stargazers';
    
    /**
     * Identifier for number of open issues
     * 
     * @var string
     */
    const OPEN_ISSUES = 'issues.open';
    
    /**
     * Identifier for number of closed issues
     * 
     * @var string
     */
    const CLOSED_ISSUES = 'issues.closed';
    
    /**
     * Number of project contributors (based on repo info).
     * 
     * @var string
     */
    const CONTRIBUTORS = 'contributors';
    
    /**
     * Number of commits (repo).
     * 
     * @var string
     */
    const COMMITS = 'commits';
    
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
     * @var string
     *
     * @ORM\Column(name="groupname", type="string", length=32, nullable=false)
     */
    private $group;

    /**
     * @var string
     *
     * @ORM\Column(name="value", type="text", nullable=false)
     */
    private $value;

    /**
     * @var \Users
     *
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=true)
     * })
     */
    private $user;

    /**
     * The related package.
     * 
     * @var Package
     * @ORM\ManyToOne(targetEntity="Package")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="package_id", referencedColumnName="id", nullable=false)
     * })
     */
    private $package;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->timeUpdated = new \DateTime();
    }
    
    /**
     * Factory method.
     * 
     * @param array $data
     * @return MetaInfo
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
     * Factory method to create metainfo based on values.
     * 
     * @param string $group
     * @param mixed  $value
     * @return MetaInfo
     */
    public static function fromValue($group, $value, $version = null)
    {
        return self::fromArray(
            array(
                'group'    => $group,
                'value'    => $value,
                'version'  => $version
            )
        );
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
     * Returns the group name.
     * 
     * @return string
     */
    public function getGroup()
    {
        return $this->group;
    }
    
    /**
     * Returns the value.
     * 
     * @return string|int
     */
    public function getValue()
    {
        return $this->value;
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
     * Set the version string.
     * 
     * @param string $version
     */
    public function setVersion($version)
    {
        $this->version = $version;
    }
    
    /**
     * Returns the id of the user who created the info.
     * 
     * @return int|null
     */
    public function getUser()
    {
        return $this->user;
    }
    
    /**
     * Set the related user.
     * 
     * @param User $user
     */
    public function setUser(User $user)
    {
        $this->user = $user;
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
}
