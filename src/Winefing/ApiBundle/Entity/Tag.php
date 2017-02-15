<?php

namespace Winefing\ApiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Winefing\ApiBundle\Entity\LanguageEnum;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\Type;

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
     * @ORM\ManyToMany(targetEntity="Winefing\ApiBundle\Entity\Article", mappedBy="tags")
     * @Groups({"articles"})
     */
    private $articles;

    /**
     * @ORM\ManyToMany(targetEntity="Winefing\ApiBundle\Entity\Domain", mappedBy="tags", fetch="EXTRA_LAZY", cascade={"persist", "merge", "detach"})
     * @Groups({"domains"})
     */
    private $domains;

    /**
     * @var
     * @Groups({"default"})
     * @Type("string")
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

    public function _construct() {
        $this->tagTrs[] = new ArrayCollection();
        $this->articles[] = new ArrayCollection();
        $this->domains[] = new ArrayCollection();
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
    public function getTagTrs() {
        return $this->tagTrs;
    }

    /**
     * @return mixed
     */
    public function getDomains()
    {
        return $this->domains;
    }

    /**
     * @param mixed $domains
     */
    public function setDomains($domains)
    {
        $this->domains = $domains;
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

}

