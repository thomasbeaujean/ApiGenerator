<?php

namespace tbn\ApiGeneratorBundle\Tests\Fixtures\AppTestBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use tbn\ApiGeneratorBundle\Tests\Fixtures\AppTestBundle\Entity\Traits;

/**
 * @author Thomas BEAUJEAN
 *
 * @ORM\Entity()
 */
class Product
{
    use Traits\IdTrait;
    use Traits\NameTrait;

    /**
     *
     * @var Category
     * @ORM\ManyToOne(targetEntity="Category", inversedBy="products")
     * @ORM\JoinColumn(name="category_id", referencedColumnName="id")
     */
    protected $category;

    /**
     *
     * @var Tag[]
     * @ORM\OneToMany(targetEntity="Tag", mappedBy="products", cascade={"ALL"})
     */
    protected $tags;

    /**
     * The creation date of the product.
     *
     * @var \DateTime
     * @ORM\Column(type="datetime", name="created_at")
     */
    protected $createdAt = null;

    /**
     * @ORM\Column(type="string", length=50,nullable=true)
     */
    protected $reference;

    /**
     *
     */
    public function __construct()
    {
        //Initialize createdAt to now (useful for new product, override by existing one)
        $this->createdAt = new \DateTime();
        $this->tags = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     *
     * @return Category
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     *
     * @param Category $category
     */
    public function setCategory(Category $category)
    {
        $this->category = $category;
    }

    /**
     *
     * @return array The tags
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * @param Tag $tag
     *
     * @return array The tags
     */
    public function addTag(Tag $tag)
    {
        $this->tags->add($tag);
    }

    /**
     *
     * @param array $tags
     */
    public function setTags($tags)
    {
        $this->tags->clear();

        $this->tags = $tags;
    }

    /**
     *
     * @return type
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     *
     * @param \DateTime $createdAt
     */
    public function setCreatedAt(\DateTime $createdAt)
    {
        $this->createdAt = $createdAt;
    }

    /**
     *
     * @return string
     */
    public function getReference()
    {
        return $this->reference;
    }

    /**
     *
     * @param string $reference
     */
    public function setReference($reference)
    {
        $this->reference = $reference;
    }
}
