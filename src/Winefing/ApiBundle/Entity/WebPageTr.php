<?php

namespace Winefing\ApiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * WebPageTr
 *
 * @ORM\Table(name="web_page_tr")
 * @ORM\Entity(repositoryClass="Winefing\ApiBundle\Repository\WebPageTrRepository")
 */
class WebPageTr
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
     * @ORM\ManyToOne(targetEntity="Winefing\ApiBundle\Entity\WebPage", inversedBy="webPageTrs", cascade={"persist", "merge"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $webPage;

    /**
     * @ORM\ManyToOne(targetEntity="Winefing\ApiBundle\Entity\Language")
     * @ORM\JoinColumn(nullable=false)
     */
    private $language;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=60)
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(name="content", type="text")
     */
    private $content;

    /**
     * @var string
     *
     * @ORM\Column(name="activated", type="boolean")
     */
    private $activated;


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
     * @return WebPageTr
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

    /**
     * Set content
     *
     * @param string $content
     *
     * @return WebPageTr
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Get content
     *
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set activated
     *
     * @param string $activated
     *
     * @return WebPageTr
     */
    public function setActivated($activated)
    {
        $this->activated = $activated;

        return $this;
    }

    /**
     * Get activated
     *
     * @return string
     */
    public function getActivated()
    {
        return $this->activated;
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
    public function getWebPage()
    {
        return $this->webPage;
    }

    /**
     * @param mixed $webPage
     */
    public function setWebPage($webPage)
    {
        $this->webPage = $webPage;
    }


}

