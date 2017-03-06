<?php

namespace Winefing\ApiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\Type;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Tag
 *
 * @ORM\Table(name="tag")
 * @ORM\Entity(repositoryClass="Winefing\ApiBundle\Repository\TagRepository")
 */
class Tag
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
     * @var
     * @ORM\OneToMany(targetEntity="Winefing\ApiBundle\Entity\TagTr", mappedBy="tag", fetch="EAGER", cascade={"all"})
     * @Groups({"trs"})
     */
    private $tagTrs;
    /**
     * @ORM\ManyToMany(targetEntity="Winefing\ApiBundle\Entity\Domain", mappedBy="tags")
     * @Groups({"articles"})
     */
    private $domains;

    /**
     * @ORM\ManyToMany(targetEntity="Winefing\ApiBundle\Entity\Article", mappedBy="tags")
     * @Groups({"articles"})
     */
    private $articles;

    /**
     * @var
     * @Groups({"default"})
     * @Type("string")
     */
    private $name;

    /**
     * @ORM\Column(name="picture", type="string", length=500, nullable=true)
     * @Groups({"default"})
     */
    protected $picture;


    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    public function __construct() {
        $this->tagTrs = new ArrayCollection();
        $this->articles = new ArrayCollection();
        $this->domains = new ArrayCollection();
    }

    public function addTagTr(TagTr $tagTr) {
        $this->tagTrs[] = $tagTr;
        $tagTr->setTag($this);
        return $this;
    }
    public function addArticle(Article $article) {
        $this->articles[] = $article;
        return $this;
    }
    public function getDomains() {
        return $this->domains;
    }
    public function getTagTrs() {
        return $this->tagTrs;
    }

    public function getDisplayName($language){
        foreach($this->tagTrs as $tag){
            if($tag->getLanguage()->getCode() == $language) {
                return $tag->getName();
                break;
            }
        }
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
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    public function setTr($language) {
        foreach($this->tagTrs as $tagTr) {
            if($tagTr->getLanguage()->getCode() == $language) {
                $this->name = $tagTr->getName();
                break;
            }
        }
    }

    /**
     * @return mixed
     */
    public function getPicture()
    {
        return $this->picture;
    }

    /**
     * @param mixed $picture
     */
    public function setPicture($picture)
    {
        $this->picture = $picture;
    }

}

