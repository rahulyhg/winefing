<?php
/**
 * Created by PhpStorm.
 * User: audreycarval
 * Date: 04/10/2016
 * Time: 13:55
 */
namespace Winefing\ApiBundle\Entity;

abstract class AddressTypeEnum extends BasicEnum {
    const address_billing = 'billing';
    const address_delivering = 'delivering';

}