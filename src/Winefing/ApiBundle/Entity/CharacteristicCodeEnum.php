<?php
/**
 * Created by PhpStorm.
 * User: audreycarval
 * Date: 04/10/2016
 * Time: 13:55
 */
namespace Winefing\ApiBundle\Entity;

abstract class CharacteristicCodeEnum extends BasicEnum {
    const WineType = 'WINE_TYPE';
    const Checkin = 'CHECK_IN';
    const Checkout = 'CHECK_OUT';
}