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
 * @ORM\Table(name="lemon_way")
 * @ORM\Entity(repositoryClass="Winefing\ApiBundle\Repository\LemonWayRepository")
 * @package Winefing\ApiBundle\Entity
 */
class LemonWay
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
     * @var float
     *
     * @ORM\Column(name="amountCom", type="float", nullable=true)
     * @Groups({"default"})
     */
    private $amountCom;

    /**
     * @var float
     *
     * @ORM\Column(name="transactionId", type="float", nullable=true)
     * @Groups({"default"})
     */
    private $transactionId;

    /**
     * @var float
     *
     * @ORM\Column(name="amountTot", type="float", nullable=true)
     * @Groups({"default"})
     */
    private $amountTot;

    /**
     * @return float
     */
    public function getAmountCom()
    {
        return $this->amountCom;
    }

    /**
     * @param float $amountCom
     */
    public function setAmountCom($amountCom)
    {
        $this->amountCom = $amountCom;
    }

    /**
     * @return float
     */
    public function getTransactionId()
    {
        return $this->transactionId;
    }

    /**
     * @param float $transactionId
     */
    public function setTransactionId($transactionId)
    {
        $this->transactionId = $transactionId;
    }

    /**
     * @return float
     */
    public function getAmountTot()
    {
        return $this->amountTot;
    }

    /**
     * @param float $amountTot
     */
    public function setAmountTot($amountTot)
    {
        $this->amountTot = $amountTot;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }


}