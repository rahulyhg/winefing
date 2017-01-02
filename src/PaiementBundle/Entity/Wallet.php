<?php
/**
 * Created by PhpStorm.
 * User: audreycarval
 * Date: 29/12/2016
 * Time: 14:09
 */
namespace PaiementBundle\Entity;
use JMS\Serializer\Annotation\Type;

/**
 * To understand the private attribut, see : http://documentation.lemonway.fr/api-fr/directkit/gerer-les-wallets/registerwallet-creation-de-wallet
 * Class Wallet
 * @package PaiementBundle\Entity
 */
class Wallet {
    /**
     * @Type("integer")
     */
    private $wallet;

    /**
     * @Type("string")
     */
    private $clientMail;

    /**
     * @Type("string")
     */
    private $clientTitle = "U";

    /**
     * @Type("string")
     */
    private $clientFirstName;

    /**
     * @Type("string")
     */
    private $clientLastName;

    /**
     * @Type("string")
     */
    private $street;

    /**
     * @Type("string")
     */
    private $postCode;

    /**
     * @Type("string")
     */
    private $city;

    /**
     * @Type("integer")
     */
    private $phoneNumber;

    /**
     * @Type("integer")
     */
    private $mobileNumber;

    /**
     * @Type("DateTime<'d-m-Y'>")
     */
    private $birthDate;

    /**
     * @Type("boolean")
     */
    private $isCompany = 0;

    /**
     * @Type("string")
     */
    private $companyName;

    /**
     * @Type("string")
     */
    private $companyDescription;

    /**
     * @Type("string")
     */
    private $companyIdentificationNumber;

    /**
     * 1 : payeur
     * 2 : bénéficiaire
     * @Type("integer")
     */
    private $payerOrBeneficiary = 1;

    /**
     * @Type("boolean")
     */
    private $isOneTimeCustomer = 0;

    /**
     * @Type("boolean")
     */
    private $isTechWallet = 0;

    /**
     * @return mixed
     */
    public function getWallet()
    {
        return $this->wallet;
    }

    /**
     * @param mixed $wallet
     */
    public function setWallet($wallet)
    {
        $this->wallet = $wallet;
    }

    /**
     * @return mixed
     */
    public function getClientMail()
    {
        return $this->clientMail;
    }

    /**
     * @param mixed $clientMail
     */
    public function setClientMail($clientMail)
    {
        $this->clientMail = $clientMail;
    }

    /**
     * @return mixed
     */
    public function getClientTitle()
    {
        return $this->clientTitle;
    }

    /**
     * @param mixed $clientTitle
     */
    public function setClientTitle($clientTitle)
    {
        $this->clientTitle = $clientTitle;
    }

    /**
     * @return mixed
     */
    public function getClientFirstName()
    {
        return $this->clientFirstName;
    }

    /**
     * @param mixed $clientFirstName
     */
    public function setClientFirstName($clientFirstName)
    {
        $this->clientFirstName = $clientFirstName;
    }

    /**
     * @return mixed
     */
    public function getClientLastName()
    {
        return $this->clientLastName;
    }

    /**
     * @param mixed $clientLastName
     */
    public function setClientLastName($clientLastName)
    {
        $this->clientLastName = $clientLastName;
    }

    /**
     * @return mixed
     */
    public function getStreet()
    {
        return $this->street;
    }

    /**
     * @param mixed $street
     */
    public function setStreet($street)
    {
        $this->street = $street;
    }

    /**
     * @return mixed
     */
    public function getPostCode()
    {
        return $this->postCode;
    }

    /**
     * @param mixed $postCode
     */
    public function setPostCode($postCode)
    {
        $this->postCode = $postCode;
    }

    /**
     * @return mixed
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * @param mixed $city
     */
    public function setCity($city)
    {
        $this->city = $city;
    }

    /**
     * @return mixed
     */
    public function getPhoneNumber()
    {
        return $this->phoneNumber;
    }

    /**
     * @param mixed $phoneNumber
     */
    public function setPhoneNumber($phoneNumber)
    {
        $this->phoneNumber = $phoneNumber;
    }

    /**
     * @return mixed
     */
    public function getMobileNumber()
    {
        return $this->mobileNumber;
    }

    /**
     * @param mixed $mobileNumber
     */
    public function setMobileNumber($mobileNumber)
    {
        $this->mobileNumber = $mobileNumber;
    }

    /**
     * @return mixed
     */
    public function getBirthDate()
    {
        return $this->birthDate;
    }

    /**
     * @param mixed $birthDate
     */
    public function setBirthDate($birthDate)
    {
        $this->birthDate = $birthDate;
    }

    /**
     * @return mixed
     */
    public function getIsCompany()
    {
        return $this->isCompany;
    }

    /**
     * @param mixed $isCompany
     */
    public function setIsCompany($isCompany)
    {
        $this->isCompany = $isCompany;
    }

    /**
     * @return mixed
     */
    public function getCompanyName()
    {
        return $this->companyName;
    }

    /**
     * @param mixed $companyName
     */
    public function setCompanyName($companyName)
    {
        $this->companyName = $companyName;
    }

    /**
     * @return mixed
     */
    public function getCompanyDescription()
    {
        return $this->companyDescription;
    }

    /**
     * @param mixed $companyDescription
     */
    public function setCompanyDescription($companyDescription)
    {
        $this->companyDescription = $companyDescription;
    }

    /**
     * @return mixed
     */
    public function getCompanyIdentificationNumber()
    {
        return $this->companyIdentificationNumber;
    }

    /**
     * @param mixed $companyIdentificationNumber
     */
    public function setCompanyIdentificationNumber($companyIdentificationNumber)
    {
        $this->companyIdentificationNumber = $companyIdentificationNumber;
    }

    /**
     * @return mixed
     */
    public function getPayerOrBeneficiary()
    {
        return $this->payerOrBeneficiary;
    }

    /**
     * @param mixed $payerOrBeneficiary
     */
    public function setPayerOrBeneficiary($payerOrBeneficiary)
    {
        $this->payerOrBeneficiary = $payerOrBeneficiary;
    }

    /**
     * @return mixed
     */
    public function getIsOneTimeCustomer()
    {
        return $this->isOneTimeCustomer;
    }

    /**
     * @param mixed $isOneTimeCustomer
     */
    public function setIsOneTimeCustomer($isOneTimeCustomer)
    {
        $this->isOneTimeCustomer = $isOneTimeCustomer;
    }

    /**
     * @return mixed
     */
    public function getIsTechWallet()
    {
        return $this->isTechWallet;
    }

    /**
     * @param mixed $isTechWallet
     */
    public function setIsTechWallet($isTechWallet)
    {
        $this->isTechWallet = $isTechWallet;
    }

}