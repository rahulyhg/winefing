<?php
/**
 * Created by PhpStorm.
 * User: audreycarval
 * Date: 04/10/2016
 * Time: 13:55
 */
namespace Winefing\ApiBundle\Entity;

abstract class StatusCodeEnum extends BasicEnum {
    const empty_response = 204;
    const not_found = 404;
    const success = 200;
}