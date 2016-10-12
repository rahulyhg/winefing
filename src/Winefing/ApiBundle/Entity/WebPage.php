<?php

namespace Winefing\ApiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

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
     */
    private $id;

    /**
     * @var WebPageTrs
     * @ORM\OneToMany(targetEntity="Winefing\ApiBundle\Entity\WebPageTr", mappedBy="webPage", fetch="EAGER", cascade="ALL")
     */
    private $webPageTrs;

    /**
     * @var Languages
     */
    private $missingLanguages;


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

}

