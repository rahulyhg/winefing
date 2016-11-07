<?php
/**
 * Created by PhpStorm.
 * User: audreycarval
 * Date: 04/10/2016
 * Time: 13:55
 */
namespace Winefing\ApiBundle\Entity;

abstract class ScopeEnum extends BasicEnum {
    const Domain = 'DOMAIN';
    const Rental = 'RENTAL';
    const Property = 'PROPERTY';
}