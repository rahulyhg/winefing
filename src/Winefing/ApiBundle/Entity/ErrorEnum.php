<?php
/**
 * Created by PhpStorm.
 * User: audreycarval
 * Date: 04/10/2016
 * Time: 13:55
 */
namespace Winefing\ApiBundle\Entity;

abstract class ErrorEnum extends BasicEnum {
    const email_existing = ['code'=>1, 'message'=> 'error.email_existing'];
    const password_format = ['code' => 2, 'message'=>'error.password_format'];
    const email_not_corresponding = ['code' => 3, 'message'=>'error.email_not_corresponding'];
    const password_not_corresponding = ['code' => 4, 'message'=>'error.password_not_corresponding'];

}