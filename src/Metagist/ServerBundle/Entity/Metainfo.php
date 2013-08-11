<?php

namespace Metagist\ServerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Metainfo
 *
 * @ORM\Table(name="metainfo")
 * @ORM\Entity
 */
class Metainfo
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
     * @var string
     *
     * @ORM\Column(name="category", type="string", length=32, nullable=false)
     */
    private $category;

    /**
     * @var string
     *
     * @ORM\Column(name="group", type="string", length=32, nullable=false)
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
     * @ORM\ManyToOne(targetEntity="Users")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     * })
     */
    private $user;

    /**
     * @var \Packages
     *
     * @ORM\ManyToOne(targetEntity="Packages")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="package_id", referencedColumnName="id")
     * })
     */
    private $package;


}
