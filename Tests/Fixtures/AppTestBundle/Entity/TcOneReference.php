<?php
namespace tbn\ApiGeneratorBundle\Tests\Fixtures\AppTestBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use tbn\ApiGeneratorBundle\Tests\Fixtures\AppTestBundle\Entity\Traits;

/**
 *
 * @author Thomas BEAUJEAN
 *
 * @ORM\Entity()
 *
 */
class TcOneReference
{
    use Traits\IdTrait;
    use Traits\NameTrait;

    /**
     * @ORM\ManyToOne(targetEntity="TcReference")
     * @ORM\JoinColumn(name="tc_reference_id", referencedColumnName="id")
     **/
    protected $tcReference;

    /**
     * @ORM\ManyToOne(targetEntity="TcManyReference", inversedBy="tcOneReferences")
     * @ORM\JoinColumn(name="tc_many_reference_id", referencedColumnName="id")
     **/
    protected $tcManyReference;

    /**
     * Set tcReference
     *
     * @param TcReference $tcReference
     * @return TcOneReference
     */
    public function setTcReference(TcReference $tcReference = null)
    {
        $this->tcReference = $tcReference;

        return $this;
    }

    /**
     * Get tcReference
     *
     * @return TcReference
     */
    public function getTcReference()
    {
        return $this->tcReference;
    }

    /**
     * Set tcManyReference
     *
     * @param \tbn\ApiGeneratorTestCaseBundle\Entity\TcManyReference $tcManyReference
     * @return TcOneReference
     */
    public function setTcManyReference(TcManyReference $tcManyReference = null)
    {
        $this->tcManyReference = $tcManyReference;

        return $this;
    }

    /**
     * Get tcManyReference
     *
     * @return \tbn\ApiGeneratorTestCaseBundle\Entity\TcManyReference
     */
    public function getTcManyReference()
    {
        return $this->tcManyReference;
    }
}
