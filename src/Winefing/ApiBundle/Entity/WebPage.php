<?php

namespace Winefing\ApiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\Type;

/**
 * WebPage
 *
 * @ORM\Table(name="web_page")
 * @ORM\Entity(repositoryClass="Winefing\ApiBundle\Repository\WebPageRepository")
 */
class WebPage
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
     * @var string
     *
     * @ORM\Column(name="code", type="string", length=255, nullable=true)
     * @Groups({"default"})
     */
    private $code;

    /**
     * @var WebPageTrs
     * @ORM\OneToMany(targetEntity="Winefing\ApiBundle\Entity\WebPageTr", mappedBy="webPage", fetch="EAGER", cascade="ALL")
     * @Groups({"trs"})
     */
    private $webPageTrs;

    /**
     * @var Languages
     */
    private $missingLanguages;


    /**
     * @var string
     * @Type("string")
     * @Groups({"default"})
     */
    private $title;

    /**
     * @var string
     * @Type("string")
     * @Groups({"default"})
     */
    private $content;


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
     * Set title
     *
     * @param string $title
     *
     * @return WebPage
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    public function __construct() {
        $this->webPageTrs = new ArrayCollection();
    }

    /**
     * @return WebPageTrs
     */
    public function getWebPageTrs()
    {
        return $this->webPageTrs;
    }

    public function addWebPageTr(WebPageTr $webPageTr)
    {
        $this->webPageTrs[] = $webPageTr;
        $webPageTr->setWebPage($this);
        return $this;
    }

    public function removeWebPageTr(WebPageTr $webPageTr)
    {
        $this->webPageTrs->removeElement($webPageTr);
    }

    /**
     * @return Languages
     */
    public function getMissingLanguages()
    {
        return $this->missingLanguages;
    }

    /**
     * @param Languages $missingLanguages
     */
    public function setMissingLanguages($missingLanguages)
    {
        $this->missingLanguages = $missingLanguages;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param string $code
     */
    public function setCode($code)
    {
        $this->code = $code;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param string $content
     */
    public function setContent($content)
    {
        $this->content = $content;
    }


    /**
     * @param mixed $name
     */
    public function setTr($language)
    {
        foreach($this->getWebPageTrs() as $tr) {
            if($tr->getLanguage()->getCode() == $language) {
                $this->title = $tr->getTitle();
                $this->content = $tr->getContent();
                break;
            }
        }
    }
}

