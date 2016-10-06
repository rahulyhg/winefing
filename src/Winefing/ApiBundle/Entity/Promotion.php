<?php

namespace Winefing\ApiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Winefing\ApiBundle\Entity\FormatEnum;

/**
 * Promotion
 *
 * @ORM\Table(name="promotion")
 * @ORM\Entity(repositoryClass="Winefing\ApiBundle\Repository\PromotionRepository")
 */
class Promotion
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
     * @var float
     *
     * @ORM\Column(name="amount", type="float")
     */
    private $amount;

    /**
     * @var string
     *
     * @ORM\Column(name="format", type="string", length=60)
     */
    private $format;

    /**
     * @var string
     *
     * @ORM\Column(name="code", type="string", length=255, nullable=true)
     */
    private $code;

    /**
     * @var float
     *
     * @ORM\Column(name="minAmount", type="float", nullable=true)
     */
    private $minAmount;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="startDate", type="datetime")
     */
    private $startDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="endDate", type="datetime", nullable=true)
     */
    private $endDate;

    /**
     * @var int
     *
     * @ORM\Column(name="numberDisponible", type="integer", nullable=true)
     */
    private $numberDisponible;

    /**
     * @var bool
     *
     * @ORM\Column(name="firstOrder", type="boolean")
     */
    private $firstOrder = false;

    /**
     * @var bool
     *
     * @ORM\Column(name="freeShipping", type="boolean")
     */
    private $freeShipping = false;

    /**
     * @var \DateTime $created
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     */
    private $created;

    /**
     * @var \DateTime $updated
     *
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(type="datetime")
     */
    private $updated;

    private $symbol;


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
     * Set amount
     *
     * @param float $amount
     *
     * @return Promotion
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * Get amount
     *
     * @return float
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * Set format
     *
     * @param string $format
     *
     * @return Promotion
     */
    public function setFormat($format)
    {
        $this->format = $format;

        return $this;
    }

    /**
     * Get format
     *
     * @return string
     */
    public function getFormat()
    {
        return $this->format;
    }

    /**
     * Set code
     *
     * @param string $code
     *
     * @return Promotion
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get code
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set minAmount
     *
     * @param float $minAmount
     *
     * @return Promotion
     */
    public function setMinAmount($minAmount)
    {
        $this->minAmount = $minAmount;

        return $this;
    }

    /**
     * Get minAmount
     *
     * @return float
     */
    public function getMinAmount()
    {
        return $this->minAmount;
    }

    /**
     * Set startDate
     *
     * @param \DateTime $startDate
     *
     * @return Promotion
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
     * @return Promotion
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
     * Set numberDisponible
     *
     * @param integer $numberDisponible
     *
     * @return Promotion
     */
    public function setNumberDisponible($numberDisponible)
    {
        $this->numberDisponible = $numberDisponible;

        return $this;
    }

    /**
     * Get numberDisponible
     *
     * @return int
     */
    public function getNumberDisponible()
    {
        return $this->numberDisponible;
    }

    /**
     * Set firstOrder
     *
     * @param boolean $firstOrder
     *
     * @return Promotion
     */
    public function setFirstOrder($firstOrder)
    {
        $this->firstOrder = $firstOrder;

        return $this;
    }

    /**
     * Get firstOrder
     *
     * @return bool
     */
    public function getFirstOrder()
    {
        return $this->firstOrder;
    }

    /**
     * Set freeShipping
     *
     * @param boolean $freeShipping
     *
     * @return Promotion
     */
    public function setFreeShipping($freeShipping)
    {
        $this->freeShipping = $freeShipping;

        return $this;
    }

    /**
     * Get freeShipping
     *
     * @return bool
     */
    public function getFreeShipping()
    {
        return $this->freeShipping;
    }

    /**
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * @return \DateTime
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    /**
     * @return mixed
     */
    public function getSymbol()
    {
        return $this->symbol;
    }

    /**
     * @param mixed $symbol
     */
    public function setSymbol($symbol)
    {
        if($this->format == FormatEnum::Monnaie) {
            $this->symbol = 'e';
        } elseif ($this->format == FormatEnum::Percentage) {
            $this->symbol = '%';
        }
        return $this;
    }
}

