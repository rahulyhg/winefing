<?php
/**
 * Created by PhpStorm.
 * User: audreycarval
 * Date: 04/10/2016
 * Time: 13:55
 */
namespace PaiementBundle\Entity;
use Winefing\ApiBundle\Entity\BasicEnum;
abstract class CardTypeEnum extends BasicEnum {
    const CB = 0;
    const VISA = 1;
    const Mastercard = 2;
}