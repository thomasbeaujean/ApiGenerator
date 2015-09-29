<?php

namespace tbn\ApiGeneratorBundle\Tests\Fixtures\AppTestBundle\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;

/**
 *
 * @author Thomas BEAUJEAN
 *
 */
trait NameTrait
{
    /**
     * @ORM\Column(type="string", length=50,nullable=false)
     */
    protected $name;

    /**
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Get the name
     *
     * @return string
     */
    public function getName()
    {
        return  $this->name;
    }
}
