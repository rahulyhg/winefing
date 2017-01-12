<?php
/**
 * Created by PhpStorm.
 * User: audreycarval
 * Date: 04/10/2016
 * Time: 13:55
 */
namespace Winefing\ApiBundle\Entity;

abstract class StatusOrderEnum extends BasicEnum {
    const initiate = 0;
    const validate = 1;
    const pay = 2;
    const cancel = 3;
}