<?php

namespace Winefing\ApiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation\Type;
use JMS\Serializer\Annotation\Expose;
use JMS\Serializer\Annotation\Groups;

/**
 * RentalOrder
 *
 * @ORM\Table(name="rental_order")
 * @ORM\Entity(repositoryClass="Winefing\ApiBundle\Repository\RentalOrderRepository")
 */
class RentalOrder
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
     * @ORM\ManyToOne(targetEntity="Winefing\ApiBundle\Entity\User", inversedBy="rentalOrders")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"user"})
     */
    private $user;

    /**
     * @var string
     *
     * @ORM\Column(name="billDate", type="date")
     * @Groups({"default"})
     */
    private $billDate;

    /**
     * @ORM\ManyToOne(targetEntity="Winefing\ApiBundle\Entity\Address", cascade="ALL")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"billingAddress"})
     */
    private $billingAddress;

    /**
     * @var string
     *
     * @ORM\Column(name="rentalName", type="string", length=255)
     * @Groups({"default"})
     */
    private $rentalName;

    /**
     * @var string
     *
     * @ORM\Column(name="propertyName", type="string", length=255)
     * @Groups({"default"})
     */
    private $propertyName;


    /**
     * @var string
     *
     * @ORM\Column(name="domainName", type="string", length=255)
     * @Groups({"default"})
     */
    private $domainName;

    /**
     * @var string
     *
     * @ORM\Column(name="billingName", type="string", length=255)
     * @Groups({"default"})
     */
    private $billingName;


    /**
     * @var Rental
     * @ORM\ManyToOne(targetEntity="Winefing\ApiBundle\Entity\Rental", inversedBy="rentalOrders", fetch="EXTRA_LAZY")
     * @Groups({"rental"})
     */
    private $rental;

    /**
     * @var dayPrices
     * @ORM\OneToMany(targetEntity="Winefing\ApiBundle\Entity\DayPrice", mappedBy="rentalOrder", fetch="EXTRA_LAZY", cascade="ALL")
     * @Groups({"dayPrices"})
     */
    private $dayPrices;

    /**
     * @ORM\OneToOne(targetEntity="Winefing\ApiBundle\Entity\RentalOrderGift", inversedBy="rentalOrder", cascade="ALL")
     * @ORM\JoinColumn(nullable=true)
     * @Groups({"rentalOrderGift"})
     */
    private $rentalOrderGift;

    /**
     * @var string
     *
     * @ORM\Column(name="startDate", type="datetime")
     * @Groups({"default"})
     */
    private $startDate;

    /**
     * @var string
     *
     * @ORM\Column(name="endDate", type="datetime")
     * @Groups({"default"})
     */
    private $endDate;

    /**
     * @var float
     *
     * @ORM\Column(name="amount", type="float")
     * @Groups({"default"})
     */
    private $amount;

    /**
     * @var float
     *
     * @ORM\Column(name="averagePrice", type="float")
     * @Groups({"default"})
     */
    private $averagePrice;

    /**
     * @var float
     *
     * @ORM\Column(name="clientComission", type="float")
     * @Groups({"default"})
     */
    private $clientComission;

    /**
     * @var float
     *
     * @ORM\Column(name="hostComission", type="float")
     * @Groups({"default"})
     */
    private $hostComission;

    /**
     * @var float
     *
     * @ORM\Column(name="hostComissionPercentage", type="float")
     * @Groups({"default"})
     */
    private $hostComissionPercentage;

    /**
     * @var int
     *
     * @ORM\Column(name="dayNumber", type="integer")
     * @Groups({"default"})
     */
    private $dayNumber;

    /**
     * @var float
     *
     * @ORM\Column(name="totalHT", type="float")
     * @Groups({"default"})
     */
    private $totalHT;

    /**
     * @var float
     *
     * @ORM\Column(name="totalTTC", type="float")
     * @Groups({"default"})
     */
    private $totalTTC;

    /**
     * @var int
     *
     * @ORM\Column(name="status", type="integer")
     * @Groups({"default"})
     */
    private $status = 0;

    /**
     * @var float
     *
     * @ORM\Column(name="reduction", type="float", nullable=true)
     * @Groups({"default"})
     */
    private $reduction;

    /**
     * @var float
     *
     * @ORM\Column(name="lemonWayTransactionId", type="float", nullable=true)
     * @Groups({"default"})
     */
    private $lemonWayTransactionId;

    /**
     * @var float
     *
     * @ORM\Column(name="lemonWayComission", type="float", nullable=true)
     * @Groups({"default"})
     */
    private $lemonWayComission;

    /**
     * @var float
     *
     * @ORM\Column(name="totalTax", type="float")
     * @Groups({"default"})
     */
    private $totalTax;

    /**
     * @var float
     *
     * @ORM\Column(name="tax", type="float")
     * @Groups({"default"})
     */
    private $tax = 20.00;

    /**
     * @var string
     *
     * @ORM\Column(name="companyName", type="string", length=255)
     * @Groups({"default"})
     */
    private $companyName ='Winefing SAS';

    /**
     * @var string
     *
     * @ORM\Column(name="rcs", type="string", length=255)
     * @Groups({"default"})
     */
    private $rcs = '819 785 577';

    /**
     * @var string
     *
     * @ORM\Column(name="siren", type="string", length=255)
     * @Groups({"default"})
     */
    private $siren = '819 785 577';

    /**
     * @var string
     *
     * @ORM\Column(name="siret", type="string", length=255)
     * @Groups({"default"})
     */
    private $siret = '819 785 577 00019';

    /**
     * @var string
     *
     * @ORM\Column(name="rcsCity", type="string", length=255)
     * @Groups({"default"})
     */
    private $rcsCity = 'Bordeaux';

    /**
     * @var string
     *
     * @ORM\Column(name="legalForm", type="string", length=255)
     * @Groups({"default"})
     */
    private $legalForm = 'SAS';

    /**
     * @var string
     *
     * @ORM\Column(name="streetWinefing", type="string", length=255)
     * @Groups({"default"})
     */
    private $streetWinefing = '6 rue de la Porte de Basse';

    /**
     * @var string
     *
     * @ORM\Column(name="postalCodeWinefing", type="string", length=255)
     * @Groups({"default"})
     */
    private $postalCodeWinefing = '33000';

    /**
     * @var string
     *
     * @ORM\Column(name="cityWinefing", type="string", length=255)
     * @Groups({"default"})
     */
    private $cityWinefing = 'Bordeaux';


    /**
     * @var string
     *
     * @ORM\Column(name="tvaNumber", type="string", length=255)
     * @Groups({"default"})
     */
    private $tvaNumber = '22819785577';

    /**
     * @var string
     *
     * @ORM\Column(name="hostCompanyName", type="string", length=255)
     * @Groups({"default"})
     */
    private $hostCompanyName;

    /**
     * @ORM\OneToOne(targetEntity="Winefing\ApiBundle\Entity\Address", cascade="ALL")
     * @ORM\JoinColumn(nullable=true)
     * @Groups({"hostCompanyAddress"})
     */
    private $hostCompanyAddress;



    public function __construct()
    {
        $this->dayPrices = new ArrayCollection();
    }

    public function __clone()
    {
        // TODO: Implement __clone() method.
    }

    /**
     * @return string
     */
    public function getCityWinefing()
    {
        return $this->cityWinefing;
    }

    /**
     * @return mixed
     */
    public function getBillingAddress()
    {
        return $this->billingAddress;
    }

    /**
     * @param mixed $billingAddress
     */
    public function setBillingAddress($billingAddress)
    {
        $this->billingAddress = $billingAddress;
    }

    /**
     * @return string
     */
    public function getBillingName()
    {
        return $this->billingName;
    }

    /**
     * @param string $billingName
     */
    public function setBillingName($billingName)
    {
        $this->billingName = $billingName;
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
     * Set rental
     *
     * @param string $rental
     *
     * @return RentalOrder
     */
    public function setRental($rental)
    {
        $this->rental = $rental;

        return $this;
    }

    /**
     * Get rental
     *
     * @return string
     */
    public function getRental()
    {
        return $this->rental;
    }

    /**
     * Set startDate
     *
     * @param string $startDate
     *
     * @return RentalOrder
     */
    public function setStartDate($startDate)
    {
        $this->startDate = $startDate;

        return $this;
    }

    /**
     * Get startDate
     *
     * @return string
     */
    public function getStartDate()
    {
        return $this->startDate;
    }

    /**
     * Set endDate
     *
     * @param string $endDate
     *
     * @return RentalOrder
     */
    public function setEndDate($endDate)
    {
        $this->endDate = $endDate;

        return $this;
    }

    /**
     * Get endDate
     *
     * @return string
     */
    public function getEndDate()
    {
        return $this->endDate;
    }

    /**
     * Set averagePrice
     *
     * @param float $averagePrice
     *
     * @return RentalOrder
     */
    public function setAveragePrice($averagePrice)
    {
        $this->averagePrice = $averagePrice;

        return $this;
    }

    /**
     * Get averagePrice
     *
     * @return float
     */
    public function getAveragePrice()
    {
        return $this->averagePrice;
    }

    /**
     * @return float
     */
    public function getClientComission()
    {
        return $this->clientComission;
    }

    /**
     * @param float $clientComission
     */
    public function setClientComission($clientComission)
    {
        $this->clientComission = $clientComission;
    }

    /**
     * @return float
     */
    public function getHostComission()
    {
        return $this->hostComission;
    }

    /**
     * @param float $hostComission
     */
    public function setHostComission($hostComission)
    {
        $this->hostComission = $hostComission;
    }

    /**
     * @return float
     */
    public function getLemonWayComission()
    {
        return $this->lemonWayComission;
    }

    /**
     * @param float $lemonWayComission
     */
    public function setLemonWayComission($lemonWayComission)
    {
        $this->lemonWayComission = $lemonWayComission;
    }

    /**
     * Set dayNumber
     *
     * @param integer $dayNumber
     *
     * @return RentalOrder
     */
    public function setDayNumber($dayNumber)
    {
        $this->dayNumber = $dayNumber;

        return $this;
    }

    /**
     * Get dayNumber
     *
     * @return int
     */
    public function getDayNumber()
    {
        return $this->dayNumber;
    }
    /**
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param int $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return dayPrices
     */
    public function getDayPrices()
    {
        return $this->dayPrices;
    }

    /**
     * @param dayPrices $dayPrices
     */
    public function setDayPrices($dayPrices)
    {
        $this->dayPrices = $dayPrices;
    }

    public function addDayPrice(DayPrice $dayPrice) {
        $this->dayPrices[] = $dayPrice;
    }

    /**
     * @return float
     */
    public function getTotalHT()
    {
        return $this->totalHT;
    }

    /**
     * @param float $totalHT
     */
    public function setTotalHT($totalHT)
    {
        $this->totalHT = $totalHT;
    }

    /**
     * @return float
     */
    public function getTotalTTC()
    {
        return $this->totalTTC;
    }

    /**
     * @param float $totalTTC
     */
    public function setTotalTTC($totalTTC)
    {
        $this->totalTTC = $totalTTC;
    }

    /**
     * @return float
     */
    public function getReduction()
    {
        return $this->reduction;
    }

    /**
     * @param float $reduction
     */
    public function setReduction($reduction)
    {
        $this->reduction = $reduction;
    }

    /**
     * @return float
     */
    public function getTotalTax()
    {
        return $this->totalTax;
    }

    /**
     * @param float $totalTax
     */
    public function setTotalTax($totalTax)
    {
        $this->totalTax = $totalTax;
    }

    /**
     * @return float
     */
    public function getTax()
    {
        return $this->tax;
    }

    /**
     * @param float $tax
     */
    public function setTax($tax)
    {
        $this->tax = $tax;
    }

    /**
     * @return string
     */
    public function getCompanyName()
    {
        return $this->companyName;
    }

    /**
     * @param string $companyName
     */
    public function setCompanyName($companyName)
    {
        $this->companyName = $companyName;
    }

    /**
     * @return string
     */
    public function getRcs()
    {
        return $this->rcs;
    }

    /**
     * @param string $rcs
     */
    public function setRcs($rcs)
    {
        $this->rcs = $rcs;
    }

    /**
     * @return string
     */
    public function getSiren()
    {
        return $this->siren;
    }

    /**
     * @param string $siren
     */
    public function setSiren($siren)
    {
        $this->siren = $siren;
    }

    /**
     * @return string
     */
    public function getSiret()
    {
        return $this->siret;
    }

    /**
     * @param string $siret
     */
    public function setSiret($siret)
    {
        $this->siret = $siret;
    }

    /**
     * @return string
     */
    public function getRcsCity()
    {
        return $this->rcsCity;
    }

    /**
     * @param string $rcsCity
     */
    public function setRcsCity($rcsCity)
    {
        $this->rcsCity = $rcsCity;
    }

    /**
     * @return string
     */
    public function getLegalForm()
    {
        return $this->legalForm;
    }

    /**
     * @param string $legalForm
     */
    public function setLegalForm($legalForm)
    {
        $this->legalForm = $legalForm;
    }

    /**
     * @return string
     */
    public function getStreetWinefing()
    {
        return $this->streetWinefing;
    }

    /**
     * @param string $streetWinefing
     */
    public function setStreetWinefing($streetWinefing)
    {
        $this->streetWinefing = $streetWinefing;
    }

    /**
     * @return string
     */
    public function getPostalCodeWinefing()
    {
        return $this->postalCodeWinefing;
    }

    /**
     * @param string $postalCodeWinefing
     */
    public function setPostalCodeWinefing($postalCodeWinefing)
    {
        $this->postalCodeWinefing = $postalCodeWinefing;
    }

    /**
     * @return string
     */
    public function getTvaNumber()
    {
        return $this->tvaNumber;
    }

    /**
     * @param string $tvaNumber
     */
    public function setTvaNumber($tvaNumber)
    {
        $this->tvaNumber = $tvaNumber;
    }

    /**
     * @return float
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param float $amount
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
    }

    /**
     * @return string
     */
    public function getBillDate()
    {
        return $this->billDate;
    }

    /**
     * @param string $billDate
     */
    public function setBillDate($billDate)
    {
        $this->billDate = $billDate;
    }

    /**
     * @return float
     */
    public function getLemonWayTransactionId()
    {
        return $this->lemonWayTransactionId;
    }

    /**
     * @param float $lemonWayTransactionId
     */
    public function setLemonWayTransactionId($lemonWayTransactionId)
    {
        $this->lemonWayTransactionId = $lemonWayTransactionId;
    }

    /**
     * @return mixed
     */
    public function getRentalOrderGift()
    {
        return $this->rentalOrderGift;
    }

    /**
     * @param mixed $rentalOrderGift
     */
    public function setRentalOrderGift($rentalOrderGift)
    {
        $this->rentalOrderGift = $rentalOrderGift;
    }

    /**
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param mixed $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * @return float
     */
    public function getHostComissionPercentage()
    {
        return $this->hostComissionPercentage;
    }

    /**
     * @param float $hostComissionPercentage
     */
    public function setHostComissionPercentage($hostComissionPercentage)
    {
        $this->hostComissionPercentage = $hostComissionPercentage;
    }

    /**
     * @return string
     */
    public function getHostCompanyName()
    {
        return $this->hostCompanyName;
    }

    /**
     * @param string $hostCompanyName
     */
    public function setHostCompanyName($hostCompanyName)
    {
        $this->hostCompanyName = $hostCompanyName;
    }

    /**
     * @return mixed
     */
    public function getHostCompanyAddress()
    {
        return $this->hostCompanyAddress;
    }

    /**
     * @param mixed $hostCompanyAddress
     */
    public function setHostCompanyAddress($hostCompanyAddress)
    {
        $this->hostCompanyAddress = $hostCompanyAddress;
    }

    /**
     * @return string
     */
    public function getRentalName()
    {
        return $this->rentalName;
    }

    /**
     * @param string $rentalName
     */
    public function setRentalName($rentalName)
    {
        $this->rentalName = $rentalName;
    }

    /**
     * @return string
     */
    public function getPropertyName()
    {
        return $this->propertyName;
    }

    /**
     * @param string $propertyName
     */
    public function setPropertyName($propertyName)
    {
        $this->propertyName = $propertyName;
    }

    /**
     * @return string
     */
    public function getDomainName()
    {
        return $this->domainName;
    }

    /**
     * @param string $domainName
     */
    public function setDomainName($domainName)
    {
        $this->domainName = $domainName;
    }
}

