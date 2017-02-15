<?php
/**
 * Created by PhpStorm.
 * User: audreycarval
 * Date: 18/10/2016
 * Time: 15:41
 */

namespace Winefing\ApiBundle\Controller;


use Symfony\Component\HttpKernel\Bundle\BundleInterface;

class WebPathController
{
    /**
     * Get path
     *
     * @return string Resolved path
     */
    public function getPath($directory)
    {
        if (isset($_SERVER['HTTPS']) &&
            ($_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] == 1) ||
            isset($_SERVER['HTTP_X_FORWARDED_PROTO']) &&
            $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https') {
            $protocol = 'https://';
        }
        else {
            $protocol = 'http://';
        }
        return $protocol.$_SERVER['HTTP_HOST'].'/'.$directory;
    }
}