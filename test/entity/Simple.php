<?php

namespace DoctrineDbalIbmiTest\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="SIMPLE_ENTITY")
 */
class Simple
{
    /**
     * @ORM\Id()
     * @ORM\Column(name="id", type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @var int
     */
    private $id;

    /**
     * @ORM\Column(name="name", type="string", length=1024, nullable=false)
     * @var string
     */
    private $name;
}
