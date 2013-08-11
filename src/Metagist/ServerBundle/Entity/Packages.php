<?php

namespace Metagist\ServerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Packages
 *
 * @ORM\Table(name="packages")
 * @ORM\Entity
 */
class Packages
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
    private $type;

    /**
     * @var string
     *
     * @ORM\Column(name="versions", type="text", nullable=false)
     */
    private $versions;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="time_updated", type="datetime", nullable=false)
     */
    private $timeUpdated;


}
