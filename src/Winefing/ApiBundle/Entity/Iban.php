<?php

namespace Winefing\ApiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\Type;
/**
 * Iban
 *
 * @ORM\Table(name="iban")
 * @ORM\Entity(repositoryClass="Winefing\ApiBundle\Repository\IbanRepository")
 */
class Iban
{

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({"id","default"})
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="bic", type="string", length=255)
     * @Groups({"default"})
     */
    private $bic;

    /**
     * @var string
     *
     * @ORM\Column(name="iban", type="string", length=255)
     * @Groups({"default"})
     */
    private $iban;

    /**
     * @ORM\OneToOne(targetEntity="Winefing\ApiBundle\Entity\Company")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"company"})
     */
    private $company;


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
     * Set bic
     *
     * @param string $bic
     *
     * @return Iban
     */
    public function setBic($bic)
    {
        $this->bic = $bic;

        return $this;
    }

    /**
     * Get bic
     *
     * @return string
     */
    public function getBic()
    {
        return $this->bic;
    }

    /**
     * Set iban
     *
     * @param string $iban
     *
     * @return Iban
     */
    public function setIban($iban)
    {
        $this->iban = $iban;

        return $this;
    }

    /**
     * Get iban
     *
     * @return string
     */
    public function getIban()
    {
        return $this->iban;
    }

    /**
     * @return mixed
     */
    public function getCompany()
    {
        return $this->company;
    }

    /**
     * @param mixed $company
     */
    public function setCompany($company)
    {
        $this->company = $company;
    }

}

