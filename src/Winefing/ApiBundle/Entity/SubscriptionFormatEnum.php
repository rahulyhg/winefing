<?php
/**
 * Created by PhpStorm.
 * User: audreycarval
 * Date: 04/10/2016
 * Time: 13:55
 */
namespace Winefing\ApiBundle\Entity;

abstract class SubscriptionFormatEnum extends BasicEnum {
    const Sms = 'SMS';
    const Email = 'EMAIL';
}