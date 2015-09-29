<?php

namespace tbn\ApiGeneratorBundle\Tests\Fixtures\AppTestBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use tbn\ApiGeneratorBundle\Tests\Fixtures\AppTestBundle\Entity\Traits;

/**
 * the doctrine mapping type:
 * http://doctrine-dbal.readthedocs.org/en/latest/reference/types.html.
 *
 *   smallint
 *   integer
 *   bigint
 *   decimal
 *   float
 *   string
 *   text
 *   guid
 *   binary
 *   blob
 *   boolean
 *   date
 *   datetime
 *   datetimetz
 *   time
 *   dateinterval
 *   array
 *   simple_array
 *   json_array
 *   object
 */

/**
 * @author Thomas BEAUJEAN
 *
 * @ORM\Entity()
 */
class TcReference
{
    use Traits\IdTrait;
    use Traits\NameTrait;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    protected $testSmallInt;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $testInteger;

    /**
     * @ORM\Column(type="bigint", nullable=true)
     */
    protected $testBigint;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    protected $testFloat;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $testBoolean = null;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    protected $testDate;

    /**
     * @ORM\Column(type="time", nullable=true)
     */
    protected $testTime;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $testDatetime;

    /**
     * @ORM\Column(type="decimal", nullable=true)
     */
    protected $testDecimal;


    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $testString;


    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $testText;

    /**
     * @ORM\Column(type="array", nullable=true)
     */
    protected $testArray;

    /**
     *
     * @return array
     */
    public function getTestArray()
    {
        return $this->testArray;
    }

    /**
     *
     * @param array $testArray
     */
    public function setTestArray($testArray)
    {
        $this->testArray = $testArray;
    }

    /**
     *
     * @return string
     */
    public function getTestText()
    {
        return $this->testText;
    }

    /**
     *
     * @param string $testText
     */
    public function setTestText($testText)
    {
        $this->testText = $testText;
    }

    /**
     *
     * @return string
     */
    public function getTestString()
    {
        return $this->testString;
    }

    /**
     *
     * @param string $testString
     */
    public function setTestString($testString)
    {
        $this->testString = $testString;
    }

    /**
     *
     * @return float
     */
    public function getTestDecimal()
    {
        return $this->testDecimal;
    }

    /**
     *
     * @param float $testDecimal
     */
    public function setTestDecimal($testDecimal)
    {
        $this->testDecimal = $testDecimal;
    }

    /**
     *
     * @return DateTime
     */
    public function getTestDatetime()
    {
        return $this->testDatetime;
    }

    /**
     *
     * @param DateTime $testDatetime
     */
    public function setTestDatetime($testDatetime)
    {
        $this->testDatetime = $testDatetime;
    }

    /**
     *
     * @return DateTime
     */
    public function getTestTime()
    {
        return $this->testTime;
    }

    /**
     *
     * @param DateTime $testTime
     */
    public function setTestTime($testTime)
    {
        $this->testTime = $testTime;
    }

    /**
     *
     * @return DateTime
     */
    public function getTestDate()
    {
        return $this->testDate;
    }

    /**
     *
     * @param DateTime $testDate
     */
    public function setTestDate($testDate)
    {
        $this->testDate = $testDate;
    }

    /**
     *
     * @return boolean
     */
    public function getTestBoolean()
    {
        return $this->testBoolean;
    }

    /**
     *
     * @param boolean $testBoolean
     */
    public function setTestBoolean($testBoolean)
    {
        $this->testBoolean = $testBoolean;
    }

    /**
     *
     * @return float
     */
    public function getTestFloat()
    {
        return $this->testFloat;
    }

    /**
     *
     * @param float $testFloat
     */
    public function setTestFloat($testFloat)
    {
        $this->testFloat = $testFloat;
    }

    /**
     *
     * @return int
     */
    public function getTestSmallInt()
    {
        return $this->testSmallInt;
    }

    /**
     *
     * @return int
     */
    public function getTestInteger()
    {
        return $this->testInteger;
    }

    /**
     *
     * @param int $testSmallInt
     */
    public function setTestSmallInt($testSmallInt)
    {
        $this->testSmallInt = $testSmallInt;
    }

    /**
     *
     * @param int $testInteger
     */
    public function setTestInteger($testInteger)
    {
        $this->testInteger = $testInteger;
    }

    /**
     *
     * @return int
     */
    public function getTestBigint()
    {
        return $this->testBigint;
    }

    /**
     *
     * @param int $testBigint
     */
    public function setTestBigint($testBigint)
    {
        $this->testBigint = $testBigint;
    }
}
