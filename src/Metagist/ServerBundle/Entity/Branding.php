<?php

namespace Metagist\ServerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\UniqueConstraint;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

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
     * update time
     * @var string
     * @ORM\Column(name="path", type="string", nullable=true)
     */
    private $path;

    /**
     * update time
     * @var \DateTime
     * @ORM\Column(name="updated_at", type="datetime")
     */
    private $updatedAt;

    /**
     * @Assert\File(maxSize="600000")
     */
    private $file;
    
    private $packages;

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
     * Get less
     *
     * @return string 
     */
    public function getLess()
    {
        return $this->less;
    }

    /**
     * Get less, surround with the vendor class.
     *
     * @return string 
     */
    public function getLessWithVendor()
    {
        return '.' . $this->vendor . '{' . PHP_EOL
            . $this->less . PHP_EOL
            . '}' . PHP_EOL;
    }

    /**
     * Get file.
     *
     * @return UploadedFile
     */
    public function getFile()
    {
        return $this->file;
    }

    public function getAbsolutePath()
    {
        return null === $this->path ? null : $this->getUploadRootDir() . '/' . $this->path;
    }

    public function getWebPath()
    {
        return null === $this->path ? null : $this->getUploadDir() . '/' . $this->path;
    }

    /**
     * the absolute directory path where uploaded documents should be saved
     * 
     * @return string
     */
    protected function getUploadRootDir()
    {
        return realpath(__DIR__ . '/../../../../web/' . $this->getUploadDir() . '/');
    }

    protected function getUploadDir()
    {
        return 'images';
    }

    /**
     * Sets file.
     *
     * @param UploadedFile $file
     */
    public function setFile(UploadedFile $file = null)
    {
        $this->file = $file;
        $this->path = $this->vendor . '.' . $this->getFile()->guessExtension();
    }

    /**
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     */
    public function preUpload()
    {
        if (null !== $this->getFile()) {
            $this->updatedAt = new \DateTime();
        }
    }

    /**
     * @ORM\PostPersist()
     * @ORM\PostUpdate()
     */
    public function upload()
    {
        if (null === $this->getFile()) {
            return;
        }

        if (!$this->getFile()->isValid()) {
            throw new \Exception('Upload error ');
        }
        // if there is an error when moving the file, an exception will
        // be automatically thrown by move(). This will properly prevent
        // the entity from being persisted to the database on error
        $this->getFile()->move($this->getUploadRootDir(), $this->path);
    }

    /**
     * @ORM\PostRemove()
     */
    public function removeUpload()
    {
        if ($file = $this->getAbsolutePath()) {
            unlink($file);
        }
    }

    public function __toString()
    {
        return $this->vendor;
    }
}
