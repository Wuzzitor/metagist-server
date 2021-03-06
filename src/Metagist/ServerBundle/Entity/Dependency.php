<?php
/**
 * Dependency.php
 * 
 * @author Daniel Pozzi <bonndan76@googlemail.com>
 */
namespace Metagist\ServerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class representing a package dependency.
 * 
 * @author Daniel Pozzi <bonndan76@googlemail.com>
 * @ORM\Table(name="dependencies")
 * @ORM\Entity(repositoryClass="DependencyRepository")
 */
class Dependency
{
    /**
     * @var integer
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * The package having the dependency
     * 
     * @var Package
     * @ORM\ManyToOne(targetEntity="Package", inversedBy="dependencies")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="package_id", referencedColumnName="id")
     * })
     */
    private $package;
    
    /**
     * Version of the package having this dependency
     * 
     * @var string
     * @ORM\Column(name="package_version", type="string", length=32, nullable=false)
     */
    private $packageVersion;
    
    /**
     * Dependency package identifier
     * 
     * @var string
     * @ORM\Column(name="identifier", type="string", length=255, nullable=false)
     */
    private $dependencyIdentifier;

    /**
     * Version of the dependency
     * 
     * @var string
     * @ORM\Column(name="version", type="string", length=32, nullable=false)
     */
    private $dependencyVersion;
    
    /**
     * flag showing it is a dev dependency.
     * 
     * @var boolean
     * @ORM\Column(name="dev", type="boolean", nullable=true)
     */
    private $isDevDependency = false;
    
    /**
     * Returns the id
     * 
     * @return int|null
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Returns the related package.
     * 
     * @return Package
     */
    public function getPackage()
    {
        return $this->package;
    }

    /**
     * Set the related package.
     * 
     * @param \Metagist\ServerBundle\Entity\Package $package
     */
    public function setPackage(Package $package)
    {
        $this->package = $package;
    }

    /**
     * Returns the version of the package having the dependency.
     * 
     * @return string
     */
    public function getPackageVersion()
    {
        return $this->packageVersion;
    }

    public function setPackageVersion($packageVersion)
    {
        $this->packageVersion = $packageVersion;
    }

    /**
     * Returns the identifier of the dependency package.
     * 
     * @return string
     */
    public function getDependencyIdentifier()
    {
        return $this->dependencyIdentifier;
    }

    /**
     * Set the identifier.
     * 
     * @param string $dependencyIdentifier
     */
    public function setDependencyIdentifier($dependencyIdentifier)
    {
        $this->dependencyIdentifier = $dependencyIdentifier;
    }

    /**
     * Returns the version string of the required dependency.
     * 
     * @return string
     */
    public function getDependencyVersion()
    {
        return $this->dependencyVersion;
    }

    /**
     * Sets the version string of the required dependency.
     * 
     * @return string
     */
    public function setDependencyVersion($dependencyVersion)
    {
        $this->dependencyVersion = $dependencyVersion;
    }
    
    /**
     * Returns the author part of the dep identifier.
     * 
     * @return string
     */
    public function getAuthor()
    {
        return current(Package::splitIdentifier($this->dependencyIdentifier));
    }
    
    /**
     * Returns the name part of the dep identifier.
     * 
     * @return string
     */
    public function getName()
    {
        $parts = Package::splitIdentifier($this->dependencyIdentifier);
        return isset($parts[1]) ? $parts[1] : '';
    }
    
    /**
     * Set the dev-dep flag.
     * 
     * @param bool $flag
     */
    public function setIsDevDependency($flag)
    {
        $this->isDevDependency = (bool)$flag;
    }
}
