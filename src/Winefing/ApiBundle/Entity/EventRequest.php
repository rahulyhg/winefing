<?php

namespace Winefing\ApiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;

/**
 * EventRequest
 *
 * @ORM\Table(name="event_request")
 * @ORM\Entity(repositoryClass="Winefing\ApiBundle\Repository\EventRequestRepository")
 */
class EventRequest
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
     * @ORM\ManyToOne(targetEntity="Winefing\ApiBundle\Entity\EventCategory")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"default"})
     */
    private $eventCategory;

    /**
     * @var float
     *
     * @ORM\Column(name="budget", type="float")
     * @Groups({"default"})
     */
    private $budget;

    /**
     * @var int
     *
     * @ORM\Column(name="peopleNumber", type="integer")
     * @Groups({"default"})
     */
    private $peopleNumber;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="startDate", type="date")
     * @Groups({"default"})
     */
    private $startDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="endDate", type="date")
     * @Groups({"default"})
     */
    private $endDate;

    /**
     * @var int
     *
     * @ORM\Column(name="duration", type="integer")
     * @Groups({"default"})
     */
    private $duration;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=255)
     * @Groups({"default"})
     */
    private $email;

    /**
     * @var string
     *
     * @ORM\Column(name="phoneNumber", type="string", length=255)
     * @Groups({"default"})
     */
    private $phoneNumber;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string", length=255)
     * @Groups({"default"})
     */
    private $description;


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
     * Set budget
     *
     * @param float $budget
     *
     * @return EventRequest
     */
    public function setBudget($budget)
    {
        $this->budget = $budget;

        return $this;
    }

    /**
     * Get budget
     *
     * @return float
     */
    public function getBudget()
    {
        return $this->budget;
    }

    /**
     * Set peopleNumber
     *
     * @param integer $peopleNumber
     *
     * @return EventRequest
     */
    public function setPeopleNumber($peopleNumber)
    {
        $this->peopleNumber = $peopleNumber;

        return $this;
    }

    /**
     * Get peopleNumber
     *
     * @return int
     */
    public function getPeopleNumber()
    {
        return $this->peopleNumber;
    }

    /**
     * Set startDate
     *
     * @param \DateTime $startDate
     *
     * @return EventRequest
     */
    public function setStartDate($startDate)
    {
        $this->startDate = $startDate;

        return $this;
    }

    /**
     * Get startDate
     *
     * @return \DateTime
     */
    public function getStartDate()
    {
        return $this->startDate;
    }

    /**
     * Set endDate
     *
     * @param \DateTime $endDate
     *
     * @return EventRequest
     */
    public function setEndDate($endDate)
    {
        $this->endDate = $endDate;

        return $this;
    }

    /**
     * Get endDate
     *
     * @return \DateTime
     */
    public function getEndDate()
    {
        return $this->endDate;
    }

    /**
     * Set duration
     *
     * @param integer $duration
     *
     * @return EventRequest
     */
    public function setDuration($duration)
    {
        $this->duration = $duration;

        return $this;
    }

    /**
     * Get duration
     *
     * @return int
     */
    public function getDuration()
    {
        return $this->duration;
    }

    /**
     * Set email
     *
     * @param string $email
     *
     * @return EventRequest
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set phoneNumber
     *
     * @param string $phoneNumber
     *
     * @return EventRequest
     */
    public function setPhoneNumber($phoneNumber)
    {
        $this->phoneNumber = $phoneNumber;

        return $this;
    }

    /**
     * Get phoneNumber
     *
     * @return string
     */
    public function getPhoneNumber()
    {
        return $this->phoneNumber;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return EventRequest
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return mixed
     */
    public function getEventCategory()
    {
        return $this->eventCategory;
    }

    /**
     * @param mixed $eventCategory
     */
    public function setEventCategory($eventCategory)
    {
        $this->eventCategory = $eventCategory;
    }
}

