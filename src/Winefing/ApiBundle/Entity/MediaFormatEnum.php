<?php
/**
 * Created by PhpStorm.
 * User: audreycarval
 * Date: 04/10/2016
 * Time: 13:55
 */
namespace Winefing\ApiBundle\Entity;

abstract class MediaFormatEnum extends BasicEnum {
    const Icon = "ICON";
    const Image = "IMAGE";
    const Video = "VIDEO";
}