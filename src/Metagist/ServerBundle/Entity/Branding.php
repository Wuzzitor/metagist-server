<?php

namespace Metagist\ServerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\UniqueConstraint;

/**
 * Branding
 *
 * @ORM\Table(name="brandings",uniqueConstraints={@UniqueConstraint(name="branding_vendor_idx", columns={"vendor"})})
 * @ORM\Entity(repositoryClass="Metagist\ServerBundle\Entity\BrandingRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Branding
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="vendor", type="string", length=128)
     */
    private $vendor;

    /**
     * @var string
     *
     * @ORM\Column(name="less", type="text")
     */
    private $less;


    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set vendor
     *
     * @param string $vendor
     * @return Branding
     */
    public function setVendor($vendor)
    {
        $this->vendor = $vendor;
    
        return $this;
    }

    /**
     * Get vendor
     *
     * @return string 
     */
    public function getVendor()
    {
        return $this->vendor;
    }

    /**
     * Set less
     *
     * @param string $less
     * @return Branding
     */
    public function setLess($less)
    {
        $this->less = $less;
    
        return $this;
    }

    /**
     * Get less, surround with the vendor class.
     *
     * @return string 
     */
    public function getLess()
    {
        return '.' . $this->vendor . '{' . PHP_EOL
            . $this->less . PHP_EOL
            . '}' . PHP_EOL;
    }
}
