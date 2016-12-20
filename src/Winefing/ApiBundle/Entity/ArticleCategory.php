<?php

namespace Winefing\ApiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Winefing\ApiBundle\Entity\LanguageEnum;
use Symfony\Component\Validator\Constraints as Assert;
/**
 * ArticleCategory
 *
 * @ORM\Table(name="article_category")
 * @ORM\Entity(repositoryClass="Winefing\ApiBundle\Repository\ArticleCategoryRepository")
 */
class ArticleCategory
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
     * @var string
     *
     * @ORM\Column(name="description", type="string", length=255)
     */
    private $description;


    /**
     * @ORM\ManyToOne(targetEntity="Winefing\ApiBundle\Entity\ArticleCategory")
     * @ORM\JoinColumn(nullable=true)
     */
    private $categoryPere;

    /**
     * @ORM\ManyToMany(targetEntity="Winefing\ApiBundle\Entity\Article", mappedBy="articleCategories")
     */
    private $articles;


    /**
     * @var ArticleCategoryTrs
     * @ORM\OneToMany(targetEntity="Winefing\ApiBundle\Entity\ArticleCategoryTr", mappedBy="articleCategory", fetch="EAGER", cascade="ALL")
     */
    private $articleCategoryTrs;

    private $hierarchy;

    private $title;

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
     * Set description
     *
     * @param string $description
     *
     * @return ArticleCategory
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getArticles()
    {
        return $this->articles;
    }

    /**
     * @param mixed $articles
     */
    public function setArticles($articles)
    {
        $this->articles = $articles;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return mixed
     */
    public function getCategoryPere()
    {
        return $this->categoryPere;
    }

    /**
     * @param mixed $categoryPere
     */
    public function setCategoryPere($categoryPere)
    {
        $this->categoryPere = $categoryPere;
    }

    public function __construct() {
        $this->articles = new ArrayCollection();
        $this->articleCategoryTrs = new ArrayCollection();
    }

    /**
     * @return ArticleCategoryTrs
     */
    public function getArticleCategoryTrs()
    {
        return $this->articleCategoryTrs;
    }
    public function addArticleCategoryTr(ArticleCategoryTr $articleCategoryTr)
    {
        $this->articleCategoryTrs[] = $articleCategoryTr;
        $articleCategoryTr->setArticleCategory($this);
        return $this;
    }

    public function removeArticleCategoryTr(ArticleCategoryTr $articleCategoryTr)
    {
        $this->articleCategoryTrs->removeElement($articleCategoryTr);
    }

    /**
     * @return mixed
     */
    public function getHierarchy()
    {
        return $this->hierarchy;
    }

    /**
     * @param mixed $hierarchy
     */
    public function setHierarchy($hierarchie)
    {
        do {
            $articleCategory = $this->getCategoryPere();
            $hierarchy = $articleCategory->getDescription().',';
        } while (!empty($this->getCategoryPere()));

        $this->hierarchy = $hierarchy;
    }

    public function getTitle() {
        foreach($this->getArticleCategoryTrs() as $tr) {
            if($tr->getLanguage()->getCode() == LanguageEnum::Français) {
                return $tr->getName();
            }
        }
    }
}

