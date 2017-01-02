<?php
/**
 * Created by PhpStorm.
 * User: audreycarval
 * Date: 29/12/2016
 * Time: 13:50
 */

namespace PaiementBundle\Entity;
use JMS\Serializer\Annotation\Type;


class CreditCard
{
    /**
     * @Type("string")
     */
    private $wallet;

    /**
     * @Type("double")
     */
    private $amountTot;

    /**
     * @Type("double")
     */
    private $amountCom;

    /**
     * @Type("string")
     */
    private $comment;

    /**
     * @Type("string")
     */
    private $wkToken;

    /**
     * @Type("string")
     */
    private $cardType;

    /**
     * @Type("string")
     */
    private $cardNumber;

    /**
     * @Type("string")
     */
    private $cardCode;

    /**
     * @Type("DateTime<'m-Y'>")
     */
    private $cardDate;

    /**
     * @Type("string")
     */
    private $autoComission;

    /**
     * @Type("string")
     */
    private $returnUrl;

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
    public function getAmountTot()
    {
        return $this->amountTot;
    }

    /**
     * @param mixed $amountTot
     */
    public function setAmountTot($amountTot)
    {
        $this->amountTot = $amountTot;
    }

    /**
     * @return mixed
     */
    public function getAmountCom()
    {
        return $this->amountCom;
    }

    /**
     * @param mixed $amountCom
     */
    public function setAmountCom($amountCom)
    {
        $this->amountCom = $amountCom;
    }

    /**
     * @return mixed
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * @param mixed $comment
     */
    public function setComment($comment)
    {
        $this->comment = $comment;
    }

    /**
     * @return mixed
     */
    public function getWkToken()
    {
        return $this->wkToken;
    }

    /**
     * @param mixed $wkToken
     */
    public function setWkToken($wkToken)
    {
        $this->wkToken = $wkToken;
    }

    /**
     * @return mixed
     */
    public function getCardType()
    {
        return $this->cardType;
    }

    /**
     * @param mixed $cardType
     */
    public function setCardType($cardType)
    {
        $this->cardType = $cardType;
    }

    /**
     * @return mixed
     */
    public function getCardNumber()
    {
        return $this->cardNumber;
    }

    /**
     * @param mixed $cardNumber
     */
    public function setCardNumber($cardNumber)
    {
        $this->cardNumber = $cardNumber;
    }

    /**
     * @return mixed
     */
    public function getCardCode()
    {
        return $this->cardCode;
    }

    /**
     * @param mixed $cardCode
     */
    public function setCardCode($cardCode)
    {
        $this->cardCode = $cardCode;
    }

    /**
     * @return mixed
     */
    public function getCardDate()
    {
        return $this->cardDate;
    }

    /**
     * @param mixed $cardDate
     */
    public function setCardDate($cardDate)
    {
        $this->cardDate = $cardDate;
    }

    /**
     * @return mixed
     */
    public function getAutoComission()
    {
        return $this->autoComission;
    }

    /**
     * @param mixed $autoComission
     */
    public function setAutoComission($autoComission)
    {
        $this->autoComission = $autoComission;
    }

    /**
     * @return mixed
     */
    public function getReturnUrl()
    {
        return $this->returnUrl;
    }

    /**
     * @param mixed $returnUrl
     */
    public function setReturnUrl($returnUrl)
    {
        $this->returnUrl = $returnUrl;
    }

}