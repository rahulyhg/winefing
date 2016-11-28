<?php

namespace Winefing\ApiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

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
     */
    private $id;

    /**
     * @var EventCategoryTr
     * @ORM\OneToMany(targetEntity="Winefing\ApiBundle\Entity\EventCategoryTr", mappedBy="eventCategory", fetch="EAGER", cascade="ALL")
     */
    private $eventCategoryTrs;

    public function _construct() {
        $this->eventCategoryTrs[] = new ArrayCollection();
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

