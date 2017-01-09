<?php
/**
 * Created by PhpStorm.
 * User: audreycarval
 * Date: 04/10/2016
 * Time: 13:55
 */
namespace Winefing\ApiBundle\Entity;

abstract class UserGroupEnum extends BasicEnum {
    const Blog = "ROLE_BLOG";
    const Technical = "ROLE_TECHNICAL";
    const Managment = "ROLE_MANAGMENT";
    const Host = "ROLE_HOST";
    const User = "ROLE_USER";
    const Admin = "ROLE_ADMIN";
}