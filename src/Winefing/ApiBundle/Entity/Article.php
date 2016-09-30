<?php

namespace Winefing\ApiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Article
 *
 * @ORM\Table(name="article")
 * @ORM\Entity(repositoryClass="Winefing\ApiBundle\Repository\ArticleRepository")
 */
class Article extends Controller
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
     * @ORM\ManyToOne(targetEntity="Winefing\ApiBundle\Entity\User")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\ManyToMany(targetEntity="Winefing\ApiBundle\Entity\ArticleCategory", inversedBy="articles", cascade={"persist", "merge", "detach"})
     */
    private $articleCategories;

    /**
     * @var ArticleTrs
     * @ORM\OneToMany(targetEntity="Winefing\ApiBundle\Entity\ArticleTr", mappedBy="article", fetch="EAGER", cascade="ALL")
     */
    private $articleTrs;

    /**
     * @var Languages
     */
    private $missingLanguages;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string", length=255)
     */
    private $description;

    /**
     * @var string
     *
     * @ORM\Column(name="picture", type="string", length=255, nullable=true)
     */
    private $picture;


    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }
    public function setId($id){
        return $this->id = $id;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return Article
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
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
     * Set picture
     *
     * @param string $picture
     *
     * @return Article
     */
    public function setPicture($picture)
    {
        $this->picture = $picture;

        return $this;
    }

    /**
     * Get picture
     *
     * @return string
     */
    public function getPicture()
    {
        return $this->picture;
    }

    /**
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param mixed $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    public function __construct() {
        $this->articleCategories = new ArrayCollection();
        $this->articleTrs = new ArrayCollection();
        $this->missingLanguages = new ArrayCollection();
    }

    /**
     * @return ArticleTrs
     */
    public function getArticleTrs()
    {
        return $this->articleTrs;
    }

    public function addArticleTr(ArticleTr $articleTr)
    {
        $this->articleTrs[] = $articleTr;
        $articleTr->setArticle($this);
        return $this;
    }
    public function addArticleCategory(ArticleCategory $articleCategory)
    {
        $this->articleCategories[] = $articleCategory;
        return $this;
    }
    public function resetArticleCategories() {
        $this->articleCategories->clear();
        return $this;
    }
    public function removeArticleTr(ArticleTr $articleTr)
    {
        $this->articleTrs->removeElement($articleTr);
    }

    /**
     * @return Language
     */
    public function getMissingLanguages()
    {
        return $this->missingLanguages;
    }
    /**
     * @return Language
     */
    public function setMissingLanguages(ArrayCollection $languages)
    {
        return $this->missingLanguages = $languages;
    }

    /**
     * @return mixed
     */
    public function getArticleCategories()
    {
        return $this->articleCategories;
    }

    /**
     * @param mixed $articleCategories
     */
    public function setArticleCategories($articleCategories)
    {
        $this->articleCategories = $articleCategories;
    }
}

