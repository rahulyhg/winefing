<?php

namespace Winefing\ApiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation\Type;
use JMS\Serializer\Annotation\Groups;

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
     * @Groups({"id", "default"})
     */
    private $id;

    /**
     * @ORM\ManyToMany(targetEntity="Winefing\ApiBundle\Entity\User", mappedBy="subscriptions")
     * @Groups({"users"})
     */
    private $users;

    /**
     * @var SubscriptionTr
     * @ORM\OneToMany(targetEntity="Winefing\ApiBundle\Entity\SubscriptionTr", mappedBy="subscription", fetch="EAGER", cascade="ALL")
     * @Groups({"trs"})
     */
    private $subscriptionTrs;

    /**
     * @var bool
     *
     * @ORM\Column(name="activated", type="boolean")
     * @Groups({"artivated"})
     */
    private $activated;

    /**
     * @var string
     *
     * @ORM\Column(name="code", type="string", length=255)
     * @Groups({"code"})
     */
    private $code;

    /**
     * @var string
     *
     * @ORM\Column(name="userGroup", type="string", length=255)
     * @Groups({"userGroup"})
     */
    private $userGroup;

    /**
     * @var string
     *
     * @ORM\Column(name="format", type="string", length=60)
     * @Groups({"format"})
     */
    private $format;

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

    /**
     * @return SubscriptionTr
     */
    public function getSubscriptionTrs()
    {
        return $this->subscriptionTrs;
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

    /**
     * @return string
     */
    public function getFormat()
    {
        return $this->format;
    }

    /**
     * @param string $format
     */
    public function setFormat($format)
    {
        $this->format = $format;
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
    public function getUserGroup()
    {
        return $this->userGroup;
    }

    /**
     * @param string $userGroup
     */
    public function setUserGroup($userGroup)
    {
        $this->userGroup = $userGroup;
    }

}

