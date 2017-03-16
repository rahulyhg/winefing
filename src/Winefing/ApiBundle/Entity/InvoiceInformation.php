<?php
/**
 * Created by PhpStorm.
 * User: audreycarval
 * Date: 07/03/2017
 * Time: 10:47
 */

namespace Winefing\ApiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation\Type;
use JMS\Serializer\Annotation\Expose;
use JMS\Serializer\Annotation\Groups;

/**
 * All the mandatory information on an invoice.
 *
 * Class Invoice
 * @ORM\Table(name="invoice_information")
 * @ORM\Entity(repositoryClass="Winefing\ApiBundle\Repository\InvoiceInformationRepository")
 * @package Winefing\ApiBundle\Entity
 */
class InvoiceInformation
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
     * @ORM\ManyToOne(targetEntity="Winefing\ApiBundle\Entity\User")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"default"})
     */
    private $user;
    /**
     * @ORM\ManyToOne(targetEntity="Winefing\ApiBundle\Entity\Address", cascade="ALL")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"default"})
     */
    private $billingAddress;

    /**
     * @ORM\ManyToOne(targetEntity="Winefing\ApiBundle\Entity\Address", cascade="ALL")
     * @ORM\JoinColumn(nullable=true)
     * @Groups({"default"})
     */
    private $deliveringAddress;
    /**
     * @var string
     *
     * @ORM\Column(name="billingName", type="string", length=255, nullable=true)
     * @Groups({"default"})
     */
    private $billingName;

    /**
     * @var string
     *
     * @ORM\Column(name="billDate", type="date")
     * @Groups({"default"})
     */
    private $billDate;

    /**
     * @var string
     *
     * @ORM\Column(name="cancelDate", type="date", nullable=true)
     * @Groups({"default"})
     */
    private $cancelDate;

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
     * @var string
     *
     * @ORM\Column(name="companyName", type="string", length=255)
     * @Groups({"default"})
     */
    private $companyName;

    /**
     * @var string
     *
     * @ORM\Column(name="rcs", type="string", length=255)
     * @Groups({"default"})
     */
    private $rcs;

    /**
     * @var string
     *
     * @ORM\Column(name="siren", type="string", length=255)
     * @Groups({"default"})
     */
    private $siren;

    /**
     * @var string
     *
     * @ORM\Column(name="siret", type="string", length=255)
     * @Groups({"default"})
     */
    private $siret;

    /**
     * @var string
     *
     * @ORM\Column(name="rcsCity", type="string", length=255)
     * @Groups({"default"})
     */
    private $rcsCity;

    /**
     * @var string
     *
     * @ORM\Column(name="legalForm", type="string", length=255)
     * @Groups({"default"})
     */
    private $legalForm;

    /**
     * @var string
     *
     * @ORM\Column(name="streetWinefing", type="string", length=255)
     * @Groups({"default"})
     */
    private $streetWinefing;

    /**
     * @var string
     *
     * @ORM\Column(name="postalCodeWinefing", type="string", length=255)
     * @Groups({"default"})
     */
    private $postalCodeWinefing;

    /**
     * @var string
     *
     * @ORM\Column(name="cityWinefing", type="string", length=255)
     * @Groups({"default"})
     */
    private $cityWinefing;


    /**
     * @var string
     *
     * @ORM\Column(name="tvaNumber", type="string", length=255)
     * @Groups({"default"})
     */
    private $tvaNumber;

    public function __construct()
    {
        $this->billDate = new \DateTime();
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
    public function getCityWinefing()
    {
        return $this->cityWinefing;
    }

    /**
     * @param string $cityWinefing
     */
    public function setCityWinefing($cityWinefing)
    {
        $this->cityWinefing = $cityWinefing;
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
     * @return mixed
     */
    public function getDeliveringAddress()
    {
        return $this->deliveringAddress;
    }

    /**
     * @param mixed $deliveringAddress
     */
    public function setDeliveringAddress($deliveringAddress)
    {
        $this->deliveringAddress = $deliveringAddress;
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
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getCancelDate()
    {
        return $this->cancelDate;
    }

    /**
     * @param string $cancelDate
     */
    public function setCancelDate($cancelDate)
    {
        $this->cancelDate = $cancelDate;
    }
}