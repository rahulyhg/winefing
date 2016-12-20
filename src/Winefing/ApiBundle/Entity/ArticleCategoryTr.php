<?php

namespace Winefing\ApiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
/**
 * ArticleCategoryTr
 *
 * @ORM\Table(name="article_category_tr")
 * @ORM\Entity(repositoryClass="Winefing\ApiBundle\Repository\ArticleCategoryTrRepository")
 */
class ArticleCategoryTr
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Winefing\ApiBundle\Entity\ArticleCategory", inversedBy="articleCategoryTrs")
     * @ORM\JoinColumn(nullable=false)
     */
    private $articleCategory;

    /**
     * @ORM\ManyToOne(targetEntity="Winefing\ApiBundle\Entity\Language")
     * @ORM\JoinColumn(nullable=false)
     */
    private $language;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=60)
     */
    private $name;


    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return ArticleCategoryTr
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return mixed
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * @param mixed $language
     */
    public function setLanguage($language)
    {
        $this->language = $language;
    }

    /**
     * @return mixed
     */
    public function getArticleCategory()
    {
        return $this->articleCategory;
    }

    /**
     * @param mixed $articleCategory
     */
    public function setArticleCategory($articleCategory)
    {
        $this->articleCategory = $articleCategory;
    }

}

