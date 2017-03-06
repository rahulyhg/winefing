<?php
/**
 * Created by PhpStorm.
 * User: audreycarval
 * Date: 27/02/2017
 * Time: 18:35
 */

namespace Winefing\ApiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\Type;
use Doctrine\Common\Collections\ArrayCollection;



class Pagination
{
    /**
     * @var
     * @Type("array<Winefing\ApiBundle\Entity\Domain>")
     * @Groups({"domains"})
     */
    protected $domains;
    /**
     * @var
     * @Type("array<Winefing\ApiBundle\Entity\Article>")
     * @Groups({"articles"})
     */
    protected $articles;
    /**
     * @Groups({"default"})
     * @Type("integer")
     */
    protected $page = 1;
    /**
     * @Groups({"default"})
     * @Type("integer")
     */
    protected $total = 1;

    public function __construct($page, $total)
    {
        $this->page = $page;
        $this->total = $total;
        $this->articles = new ArrayCollection();
        $this->domains = new ArrayCollection();
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
    public function getPage()
    {
        return $this->page;
    }

    /**
     * @return mixed
     */
    public function getTotal()
    {
        return $this->total;
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
}