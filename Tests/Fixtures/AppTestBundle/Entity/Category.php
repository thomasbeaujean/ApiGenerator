<?php

namespace tbn\ApiGeneratorBundle\Tests\Fixtures\AppTestBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use tbn\ApiGeneratorBundle\Tests\Fixtures\AppTestBundle\Entity\Traits;

/**
 * @author Thomas BEAUJEAN
 *
 * @ORM\Entity()
 */
class Category
{
    use Traits\NameTrait;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer",nullable=false)
     */
    protected $id;

    /**
     * @ORM\OneToMany(targetEntity="Product", mappedBy="category")
     **/
    protected $products;

    /**
     *
     * @param integer $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * Get the id
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     *
     */
    public function __construct()
    {
        $this->products = new \Doctrine\Common\Collections\ArrayCollection();
    }
}
