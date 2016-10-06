<?php

namespace Winefing\ApiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Subscription
 *
 * @ORM\Table(name="subscription")
 * @ORM\Entity(repositoryClass="Winefing\ApiBundle\Repository\SubscriptionRepository")
 */
class Subscription
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
     * @ORM\ManyToMany(targetEntity="Winefing\ApiBundle\Entity\User", mappedBy="subscriptions")
     */
    private $users;

    /**
     * @var SubscriptionTr
     * @ORM\OneToMany(targetEntity="Winefing\ApiBundle\Entity\SubscriptionTr", mappedBy="subscription", fetch="EAGER", cascade="ALL")
     */
    private $subscriptionTrs;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string", length=255, nullable=true)
     */
    private $description;

    /**
     * @var bool
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
     * Set description
     *
     * @param string $description
     *
     * @return Subscription
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
     * Set activated
     *
     * @param boolean $activated
     *
     * @return Subscription
     */
    public function setActivated($activated)
    {
        $this->activated = $activated;

        return $this;
    }

    /**
     * Get activated
     *
     * @return bool
     */
    public function getActivated()
    {
        return $this->activated;
    }

    /**
     * @return mixed
     */
    public function getUsers()
    {
        return $this->users;
    }

    public function __construct() {
        $this->subscriptionTrs = new ArrayCollection();
    }

    public function addSubscriptionTr(SubscriptionTr $subscriptionTr)
    {
        $this->subscriptionTrs[] = $subscriptionTr;
        $subscriptionTr->setSubscription($this);
        return $this;
    }

    public function removeSubscriptionTr(SubscriptionTr $subscriptionTr)
    {
        $this->subscriptionTrs->removeElement($subscriptionTr);
    }
}

