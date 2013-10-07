<?php

/**
 * Image.php
 * 
 * @author Daniel Pozzi <bonndan76@googlemail.com>
 * @link http://symfony.com/doc/current/cookbook/doctrine/file_uploads.html
 */

namespace Metagist\ServerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\UniqueConstraint;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;
use Metagist\ServerBundle\Entity\Package;

/**
 * Class representing a package image / thumbnail.
 * 
 * @author Daniel Pozzi <bonndan76@googlemail.com>
 * @ORM\Table(name="images" ,uniqueConstraints={@UniqueConstraint(name="package_image_idx", columns={"package_id"})})
 * @ORM\Entity(repositoryClass="ImageRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Image
{

    /**
     * @var integer
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * The package having the image
     * 
     * @var Package
     * @ORM\OneToOne(targetEntity="Package", inversedBy="image")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="package_id", referencedColumnName="id")
     * })
     */
    private $package;

    /**
     * update time
     * @var string
     * @ORM\Column(name="path", type="string")
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
     * Set the related package.
     * 
     * @param \Metagist\ServerBundle\Entity\Package $package
     */
    public function setPackage(Package $package)
    {
        $this->package = $package;
    }

    protected function getUploadRootDir()
    {
        // the absolute directory path where uploaded
        // documents should be saved
        return realpath(__DIR__ . '/../../../../web/' . $this->getUploadDir() .'/') ;
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
        $filename = str_replace('/', '_', $this->package->getIdentifier());
        $this->path = $filename . '.' . $this->getFile()->guessExtension();
        
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

}