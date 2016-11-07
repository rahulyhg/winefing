<?php

namespace Winefing\ApiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Winefing\ApiBundle\Entity\LanguageEnum;

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
     */
    private $id;

    /**
     * @var
     * @ORM\OneToMany(targetEntity="Winefing\ApiBundle\Entity\TagTr", mappedBy="tag", fetch="EAGER", cascade={"all"})
     */
    private $tagTrs;

    /**
     * @ORM\ManyToMany(targetEntity="Winefing\ApiBundle\Entity\Article", mappedBy="tags")
     */
    private $articles;

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

    public function _construct() {
        $this->tagTrs[] = new ArrayCollection();
        $this->articles[] = new ArrayCollection();
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

    public function getTitle(){
        foreach($this->getTagTrs() as $tagTr) {
            if ($tagTr->getLanguage()->getCode() == LanguageEnum::Français) {
                $this->title = $tagTr->getName();
                break;
            }
        }
        return $this->title;
    }
}

