<?php

namespace tbn\ApiGeneratorBundle\Tests\Fixtures\AppTestBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use tbn\ApiGeneratorBundle\Tests\Fixtures\AppTestBundle\Entity\Traits;

/**
 * @author Thomas BEAUJEAN
 *
 * @ORM\Entity()
 */
class Tag
{
    use Traits\IdTrait;
    use Traits\NameTrait;

    /**
     *
     * @var Product
     * @ORM\ManyToOne(targetEntity="Product", inversedBy="tags")
     * @ORM\JoinColumn(name="product_id", referencedColumnName="id")
     */
    protected $product;
}
