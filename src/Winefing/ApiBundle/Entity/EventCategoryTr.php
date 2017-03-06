<?php

namespace Winefing\ApiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation\Expose;
use JMS\Serializer\Annotation\Groups;
/**
 * EventCategoryTr
 *
 * @ORM\Table(name="event_category_tr")
 * @ORM\Entity(repositoryClass="Winefing\ApiBundle\Repository\EventCategoryTrRepository")
 */
class EventCategoryTr
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
     * @ORM\ManyToOne(targetEntity="Winefing\ApiBundle\Entity\EventCategory", inversedBy="eventCategoryTrs")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"default"})
     */
    private $eventCategory;

    /**
     * @ORM\ManyToOne(targetEntity="Winefing\ApiBundle\Entity\Language")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"default"})
     */
    private $language;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     * @Groups({"default"})
     */
    private $name;


    public function __construct(){
        $this->eventCategory = new ArrayCollection();
    }


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
     * Set name
     *
     * @param string $name
     *
     * @return EventCategoryTr
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return mixed
     */
    public function getEventCategory()
    {
        return $this->eventCategory;
    }

    /**
     * @return mixed
     */
    public function getLanguage()
    {
        return $this->language;
    }
}

