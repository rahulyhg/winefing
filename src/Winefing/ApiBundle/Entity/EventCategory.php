<?php

namespace Winefing\ApiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation\Expose;
use JMS\Serializer\Annotation\Groups;
/**
 * EventCategory
 *
 * @ORM\Table(name="event_category")
 * @ORM\Entity(repositoryClass="Winefing\ApiBundle\Repository\EventCategoryRepository")
 */
class EventCategory
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
     * @var EventCategoryTr
     * @ORM\OneToMany(targetEntity="Winefing\ApiBundle\Entity\EventCategoryTr", mappedBy="eventCategory", fetch="EAGER", cascade="ALL")
     * @Groups({"default"})
     */
    private $eventCategoryTrs;

    public function __construct() {
        $this->eventCategoryTrs = new ArrayCollection();
        return $this;
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
     * @return EventCategoryTr
     */
    public function getEventCategoryTrs()
    {
        return $this->eventCategoryTrs;
    }
}

