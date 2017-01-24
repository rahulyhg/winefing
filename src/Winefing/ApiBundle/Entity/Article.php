<?php

namespace Winefing\ApiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\Type;

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
     * @Groups({"id", "default"})
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Winefing\ApiBundle\Entity\User")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"user"})
     */
    private $user;

    /**
     * @ORM\ManyToMany(targetEntity="Winefing\ApiBundle\Entity\ArticleCategory", inversedBy="articles", cascade={"persist", "merge", "detach"})
     * @Groups({"articleCategories"})
     */
    private $articleCategories;

    /**
     * @ORM\ManyToMany(targetEntity="Winefing\ApiBundle\Entity\Tag", inversedBy="articles", cascade={"persist", "merge", "detach"})
     * @Groups({"tags"})
     */
    private $tags;

    /**
     * @var ArticleTrs
     * @ORM\OneToMany(targetEntity="Winefing\ApiBundle\Entity\ArticleTr", mappedBy="article", fetch="EAGER", cascade="ALL")
     * @Groups({"trs"})
     */
    private $articleTrs;

    /**
     * @var Languages
     */
    private $missingLanguages;

    /**
     * @var
     * @Groups({"default"})
     * @Type("string")
     */
    private $title;
    /**
     * @var
     * @Groups({"default"})
     * @Type("string")
     */
    private $shortDescription;
    /**
     * @var
     * @Groups({"default"})
     * @Type("string")
     */
    private $content;
    /**
     * @var
     * @Groups({"default"})
     * @Type("string")
     */
    private $created;
    /**
     * @var
     * @Groups({"default"})
     * @Type("string")
     */
    private $updated;

    /**
     * @var string
     *
     * @ORM\Column(name="picture", type="string", length=255, nullable=true)
     * @Groups({"default"})
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
        $this->tags = new ArrayCollection();
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
    public function resetTags() {
        $this->tags->clear();
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
    public function setMissingLanguages($languages)
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

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param mixed $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return mixed
     */
    public function getShortDescription()
    {
        return $this->shortDescription;
    }

    /**
     * @param mixed $shortDescription
     */
    public function setShortDescription($shortDescription)
    {
        $this->shortDescription = $shortDescription;
    }

    /**
     * @return mixed
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param mixed $content
     */
    public function setContent($content)
    {
        $this->content = $content;
    }

    /**
     * @return mixed
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * @param mixed $created
     */
    public function setCreated($created)
    {
        $this->created = $created;
    }

    /**
     * @return mixed
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    /**
     * @param mixed $updated
     */
    public function setUpdated($updated)
    {
        $this->updated = $updated;
    }

    public function getTags(){
        return $this->tags;
    }

    public function addTag(Tag $tag) {
        $this->tags[] = $tag;
        return $this;
    }
    public function setTr($language) {
        foreach($this->articleTrs as $articleTr) {
            if($articleTr->getLanguage()->getCode() == $language) {
                $this->title = $articleTr->getTitle();
                $this->content = $articleTr->getContent();
                $this->shortDescription = $articleTr->getShortDescription();
                $this->created = $articleTr->getCreated();
                $this->updated = $articleTr->getUpdated();
            }
        }
    }
}

