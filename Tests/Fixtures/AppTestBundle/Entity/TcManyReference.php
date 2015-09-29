<?php
namespace tbn\ApiGeneratorBundle\Tests\Fixtures\AppTestBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use tbn\ApiGeneratorBundle\Tests\Fixtures\AppTestBundle\Entity\Traits;

/**
 *
 * @author Thomas BEAUJEAN
 *
 * @ORM\Entity()
 *
 */
class TcManyReference
{
    use Traits\IdTrait;
    use Traits\NameTrait;

    /**
     * @ORM\OneToMany(targetEntity="TcOneReference", mappedBy="tcManyReference")
     **/
    protected $tcOneReferences;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->tcOneReferences = [];
    }

    /**
     * Add tcOneReferences
     *
     * @param \tbn\ApiGeneratorTestCaseBundle\Entity\TcOneReference $tcOneReferences
     * @return TcManyReference
     */
    public function addTcOneReference(TcOneReference $tcOneReferences)
    {
        $tcOneReferences->setTcManyReference($this);
        $this->tcOneReferences[] = $tcOneReferences;

        return $this;
    }

    /**
     * Remove tcOneReferences
     *
     * @param \tbn\ApiGeneratorTestCaseBundle\Entity\TcOneReference $tcOneReferences
     */
    public function removeTcOneReference(TcOneReference $tcOneReferences)
    {
        $this->tcOneReferences->removeElement($tcOneReferences);
    }

    /**
     * Get tcOneReferences
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getTcOneReferences()
    {
        return $this->tcOneReferences;
    }
}
