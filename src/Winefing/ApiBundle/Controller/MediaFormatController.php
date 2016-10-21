<?php
/**
 * Created by PhpStorm.
 * User: audreycarval
 * Date: 18/10/2016
 * Time: 15:41
 */

namespace Winefing\ApiBundle\Controller;


use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Winefing\ApiBundle\Entity\MediaFormatEnum;

class MediaFormatController
{
    protected $iconExtentions = array("svg");
    protected $imageExtentions = array("png", "jpg", "jpeg");
    protected $videoExtentions = array("mp3");

    /**
     * Get path
     *
     * @return string Resolved path
     */
    public function checkFormat($extention, $format)
    {
        $extentionsPossible = $this->getMediaFormatExtentionsPossible($format);
        if(in_array($extention, $extentionsPossible)) {
            $result = True;
        } else {
            $result = $this->getErrorMessage($extention, $format);
        }
        return $result;
    }

    public function getErrorMessage($wrongExtention, $format) {
        return 'The format '.$wrongExtention.' is not a good format for '.$format.'. Use '
        .implode(",", $this->getMediaFormatExtentionsPossible($format)).' instead.';
    }

    public function getMediaFormatExtentionsPossible($format) {
        if($format == MediaFormatEnum::Icon) {
            $extentions = $this->iconExtentions;
        } elseif ($format == MediaFormatEnum::Image) {
            $extentions = $this->imageExtentions;
        } elseif($format == MediaFormatEnum::Video) {
            $extentions = $this->videoExtentions;
        }
        return $extentions;
    }

    /**
     * @return array
     */
    public function getImageExtentions()
    {
        return $this->imageExtentions;
    }

    /**
     * @return array
     */
    public function getIconExtentions()
    {
        return $this->iconExtentions;
    }

    /**
     * @return array
     */
    public function getVideoExtentions()
    {
        return $this->videoExtentions;
    }

}